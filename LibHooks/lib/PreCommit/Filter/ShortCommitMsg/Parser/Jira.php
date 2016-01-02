<?php
namespace PreCommit\Filter\ShortCommitMsg\Parser;

use PreCommit\Config;
use PreCommit\Exception;
use PreCommit\Interpreter\InterpreterInterface;
use PreCommit\Issue;
use PreCommit\Message;

/**
 * Class filter to parse short message
 *
 * @package PreCommit\Filter\ShortCommitMsg\Jira
 */
class Jira implements InterpreterInterface
{
    /**
     * Issue adapter
     *
     * @var Issue\AdapterInterface
     */
    protected $issue;

    /**
     * Message interpreting type
     *
     * @var string
     */
    protected $type;

    /**
     * Set type
     *
     * @param array $options
     * @throws \PreCommit\Exception
     */
    public function __construct(array $options = array())
    {
        if (isset($options['type'])) {
            $this->type = $options['type'];
        } else {
            $this->type = $this->getConfig()->getNode('hooks/commit-msg/message/type');
        }
        if (!$this->type) {
            throw new Exception('Type is not set.');
        }
    }

    /**
     * Filter commit message
     *
     * @param \PreCommit\Message $message
     * @return string
     */
    public function interpret($message)
    {
        //interpret first row to get summary
        $interpretResult = $this->interpretShortMessage($message->head);
        if (!$interpretResult) {
            return $message;
        }
        list($verb, $issueKey, $userBodyInline) = $interpretResult;
        $userBody = $this->mergeComment($message->userBody, $userBodyInline);

        $this->initIssue($issueKey);

        if (!$this->getIssue()) {
            //ignore when could not get an issue to build a full message
            return false;
        }

        $message->shortVerb = $verb;
        $message->issueKey  = $issueKey;
        $message->userBody  = $userBody;
        $message->issue     = $this->getIssue();
        $message->summary   = $this->getIssue()->getSummary();
        $message->verb      = $this->getVerb($verb);

        return $message;
    }

    /**
     * Get config model
     *
     * @return Config
     */
    protected function getConfig()
    {
        return Config::getInstance();
    }

    /**
     * Get verb by issue type
     *
     * @return string
     * @throws \PreCommit\Exception
     */
    protected function getIssueTypeDefaultVerb()
    {
        $generalType = $this->getIssue()->getType();
        if ($generalType) {
            return $this->interpretShortVerb(
                $this->getDefaultShortVerb($generalType)
            );
        }

        return null;
    }

    /**
     * Interpret short verb to full verb
     *
     * @param string|null $shortVerb
     * @return string
     * @throws \PreCommit\Exception
     */
    protected function interpretShortVerb($shortVerb)
    {
        $map = $this->getVerbs();
        if (!isset($map[$shortVerb])) {
            throw new Exception("Unknown verb key '$shortVerb'.");
        }

        return $map[$shortVerb];
    }

    /**
     * Get full verb
     *
     * @param string|null $shortVerb
     * @return string
     * @throws \PreCommit\Exception
     */
    protected function getVerb($shortVerb)
    {
        if (!$shortVerb) {
            return $this->getIssueTypeDefaultVerb();
        }

        return $this->interpretShortVerb($shortVerb);
    }

    /**
     * Get verbs map
     *
     * @return array
     */
    protected function getVerbs()
    {
        return (array) $this->getConfig()->getNodeArray('hooks/commit-msg/message/verb/list/'.$this->type)
            ?: (array) $this->getConfig()->getNodeArray('hooks/commit-msg/message/verb/list/default');
    }

    /**
     * Convert issue number to issue key
     *
     * Add project key to issue number when it did not set.
     *
     * @return string
     * @throws \PreCommit\Exception
     */
    protected function getActiveIssueKey()
    {
        return $this->getConfig()->getNode('tracker/'.$this->getTrackerType().'/active_task');
    }

    /**
     * Convert issue number to issue key
     *
     * Add project key to issue number when it did not set.
     *
     * @param string $issueNo
     * @return string
     * @throws \PreCommit\Exception
     */
    protected function normalizeIssueKey($issueNo)
    {
        if ((string) (int) $issueNo === $issueNo) {
            $project = $this->getConfig()->getNode('tracker/'.$this->getTrackerType().'/project');
            if (!$project) {
                throw new Exception(
                    'JIRA project key is not set. Please add it to issue-key or add by XPath "tracker/jira/project" in project configuration file "commithook.xml" within current project.'
                );
            }
            $issueNo = "$project-$issueNo";
        }

        return $issueNo;
    }

    /**
     * Interpret message title
     *
     * @param string $message
     * @return array
     * @throws \PreCommit\Exception
     * @todo Too long and complex code
     */
    protected function interpretShortMessage($message)
    {
        $verbs           = array_keys($this->getVerbs());
        $verbs           = implode('|', $verbs);
        $verbs           = "$verbs";
        $issueKeyRegular = $this->getIssueKeyRegular();
        preg_match("/^(($verbs) ?)?(($issueKeyRegular) ?)?([^\n]{2,})?/", trim($message), $m);

        if (!$m || !$m[0]) {
            /**
             * Issue not found. Try to get one from defined configuration
             */
            $issueNo = $this->getActiveIssueKey();
            if (!$issueNo || !preg_match("/^$issueKeyRegular$/", $issueNo)) {
                //no chances to find an issue
                return false;
            }

            return array(null, $this->normalizeIssueKey($issueNo), null);
        }

        $commitVerb = trim($m[2]);
        if ($commitVerb && $commitVerb.end($m) === $m[0]) {
            //only user message or issue set
            $commitVerb = null;
        }
        if ($commitVerb && strlen($m[0]) > 1 && strlen($m[1]) == strlen($m[2])) {
            //"verb" letter is a part of word
            $commitVerb = null;
        }
        if ($commitVerb && strlen($m[0]) == 1) {
            /**
             * Issue not found. Try to get one from defined configuration
             */
            $issueNo = $this->getActiveIssueKey();
            if (!$issueNo || !preg_match("/^$issueKeyRegular$/", $issueNo)) {
                //no chances to find an issue
                return false;
            }

            return array($commitVerb, $this->normalizeIssueKey($issueNo), null);
        }

        //TODO Fix this hardcoded logic
        if (null === $commitVerb && $m[2] && $m[4] && trim($m[2]) == $m[2]) {
            //recover issue key
            $m[4] = $m[2].$m[4];
        } elseif (null === $commitVerb && isset($m[6]) && $m[6] && !$m[4] && trim($m[2]) == $m[2]) {
            //recover user message
            $m[6] = $m[2].$m[6];
        }

        //region Get issue key from matches
        $issueNo = trim(@$m[4]);
        if (!$issueNo && preg_match("/[A-Z0-9]+[-][0-9]+/", $m[0])) {
            /**
             * Case when issue key already set
             * This case should be considered as a complete commit message
             */
            return false;
        }
        if (!$issueNo) {
            /**
             * Try to get it from user message match (last one)
             *
             * This case possible for such format:
             *     R PRJ-123
             *     user-message
             */
            end($m);
            $issueNo = trim(current($m));
            if (preg_match("/^$issueKeyRegular$/", $issueNo)) {
                $m = array(); //no user message in first row, only issue key.
            } else {
                $issueNo = null;
            }
        }
        if (!$issueNo) {
            /**
             * Issue not found. Try to get one from defined configuration
             */
            $issueNo = $this->getActiveIssueKey();
            if (!preg_match("/^$issueKeyRegular$/", $issueNo)) {
                //no chances to find an issue
                return false;
            }
        }
        $issueKey = $this->normalizeIssueKey($issueNo);
        //endregion

        $userMessage = null;
        if ($m) {
            $userMessage = trim(array_pop($m));
        }

        return array($commitVerb, $issueKey, $userMessage);
    }

    /**
     * Get default short verb by general issue type
     *
     * @param string $generalType
     * @return string|null
     */
    protected function getDefaultShortVerb($generalType)
    {
        return $this->getConfig()->getNode('filters/ShortCommitMsg/issue/default_type_verb/'.$generalType);
    }

    /**
     * Merge inline comment
     *
     * @param string $comment
     * @param string $commentInline
     * @return string
     */
    protected function mergeComment($comment, $commentInline)
    {
        if ($commentInline && $comment) {
            $comment = $commentInline."\n".$comment;
        } elseif ($commentInline) {
            $comment = $commentInline;
        }

        return $comment;
    }

    /**
     * Initialize issue adapter
     *
     * @param string $issueKey
     * @return \PreCommit\Issue\AdapterInterface
     * @throws \PreCommit\Exception
     */
    protected function initIssue($issueKey)
    {
        $this->issue = Issue::factory($issueKey);

        return $this;
    }

    /**
     * Get issue
     *
     * @return \PreCommit\Issue\AdapterInterface
     */
    protected function getIssue()
    {
        return $this->issue;
    }

    /**
     * Get issue key regular expression
     *
     * @return string
     */
    protected function getIssueKeyRegular()
    {
        return '([A-Z0-9]+[-])?[0-9]+';
    }

    /**
     * Get issue key regular expression
     *
     * @return string
     */
    protected function getIssueKeyCompleteRegular()
    {
        return '[A-Z0-9]+[-][0-9]+';
    }

    /**
     * Get tracker type
     *
     * @return string
     */
    protected function getTrackerType()
    {
        return (string) $this->getConfig()->getNode('tracker/type');
    }
}
