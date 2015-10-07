<?php
namespace PreCommit\Filter\ShortCommitMsg;

use PreCommit\Config;
use PreCommit\Exception;
use PreCommit\Interpreter\InterpreterInterface;
use PreCommit\Issue;
use PreCommit\Jira\Api;
use PreCommit\Message;

/**
 * Class filter to parse short message
 *
 * @package PreCommit\Validator
 */
class Parser implements InterpreterInterface
{
    /**
     * Issue adapter
     *
     * @var Issue\AdapterInterface
     */
    protected $_issue;

    /**
     * Message interpreting type
     *
     * @var string
     */
    protected $_type;

    /**
     * Set type
     *
     * @param array $options
     * @throws \PreCommit\Exception
     */
    public function __construct(array $options = array())
    {
        if (isset($options['type'])) {
            $this->_type = $options['type'];
        } else {
            $this->_type = $this->_getConfig()->getNode('hooks/commit-msg/message/type');
        }
        if (!$this->_type) {
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
        $interpretResult = $this->_interpretShortMessage($message->head);
        if (!$interpretResult) {
            return $message;
        }
        list($verb, $issueKey, $userBodyInline) = $interpretResult;
        $userBody = $this->_mergeComment($message->userBody, $userBodyInline);

        $this->_initIssue($issueKey);

        if (!$this->_getIssue()) {
            //ignore when could not get an issue to build a full message
            return false;
        }

        $message->shortVerb = $verb;
        $message->issueKey  = $issueKey;
        $message->userBody  = $userBody;
        $message->issue     = $this->_getIssue();
        $message->summary   = $this->_getIssue()->getSummary();
        $message->verb      = $this->_getVerb($verb);

        return $message;
    }

    /**
     * Get config model
     *
     * @return Config
     */
    protected function _getConfig()
    {
        return Config::getInstance();
    }

    /**
     * Get verb by issue type
     *
     * @return string
     * @throws \PreCommit\Exception
     */
    protected function _getIssueTypeDefaultVerb()
    {
        $generalType = $this->_getIssue()->getType();
        if ($generalType) {
            return $this->_interpretShortVerb(
                $this->_getDefaultShortVerb($generalType)
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
    protected function _interpretShortVerb($shortVerb)
    {
        $map = $this->_getVerbs();
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
    protected function _getVerb($shortVerb)
    {
        if (!$shortVerb) {
            return $this->_getIssueTypeDefaultVerb();
        }
        return $this->_interpretShortVerb($shortVerb);
    }

    /**
     * Get verbs map
     *
     * @return array
     */
    protected function _getVerbs()
    {
        return (array)$this->_getConfig()->getNodeArray('hooks/commit-msg/message/verb/list/' . $this->_type)
            ?: (array)$this->_getConfig()->getNodeArray('hooks/commit-msg/message/verb/list/default');
    }

    /**
     * Convert issue number to issue key
     *
     * Add project key to issue number when it did not set.
     *
     * @return string
     * @throws \PreCommit\Exception
     * @todo Refactor this 'cos it belongs to JIRA only
     */
    protected function _getActiveIssueKey()
    {
        return $this->_getConfig()->getNode('tracker/jira/active_task');
    }

    /**
     * Convert issue number to issue key
     *
     * Add project key to issue number when it did not set.
     *
     * @param string $issueNo
     * @return string
     * @throws \PreCommit\Exception
     * @todo Refactor this 'cos it belongs to JIRA only
     */
    protected function _normalizeIssueKey($issueNo)
    {
        if ((string)(int)$issueNo === $issueNo) {
            $project = $this->_getConfig()->getNode('tracker/jira/project');
            if (!$project) {
                throw new Exception('JIRA project key is not set. Please add it to issue-key or add by XPath "jira/project" in project configuration file "commithook.xml" within current project.');
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
     */
    protected function _interpretShortMessage($message)
    {
        $verbs = array_keys($this->_getVerbs());
        $verbs = implode('|', $verbs);
        $verbs = "($verbs)";
        $issueKeyRegular = $this->_getIssueKeyRegular();
        preg_match("/^($verbs )?({$issueKeyRegular} )?([^\n]+)?/", trim($message), $m);
        if (!$m) {
            return false;
        }
        //skip first match
        array_shift($m);
        array_shift($m);
        array_shift($m);

        $commitVerb  = trim(array_shift($m));

        //region Get issue key from matches
        $issueNo = trim(array_shift($m));
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
            $issueNo = $this->_getActiveIssueKey();
            if (!preg_match("/^$issueKeyRegular$/", $issueNo)) {
                //no chances to find an issue
                return false;
            }
        }
        $issueKey = $this->_normalizeIssueKey($issueNo);
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
    protected function _getDefaultShortVerb($generalType)
    {
        return $this->_getConfig()->getNode('filters/ShortCommitMsg/issue/default_type_verb/' . $generalType);
    }

    /**
     * Merge inline comment
     *
     * @param string $comment
     * @param string $commentInline
     * @return string
     */
    protected function _mergeComment($comment, $commentInline)
    {
        if ($commentInline && $comment) {
            $comment = $commentInline . "\n" . $comment;
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
    protected function _initIssue($issueKey)
    {
        $this->_issue = Issue::factory($issueKey);
        return $this;
    }

    /**
     * Get issue
     *
     * @return \PreCommit\Issue\AdapterInterface
     */
    protected function _getIssue()
    {
        return $this->_issue;
    }

    /**
     * Get issue key regular expression
     *
     * @return string
     */
    protected function _getIssueKeyRegular()
    {
        return '(([A-Z0-9]+[-])?[0-9]+)';
    }
}
