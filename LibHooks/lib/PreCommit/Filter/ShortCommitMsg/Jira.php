<?php
namespace PreCommit\Filter\ShortCommitMsg;

use PreCommit\Config;
use PreCommit\Exception;
use PreCommit\Filter\InterfaceFilter;
use PreCommit\Issue;
use PreCommit\Jira\Api;

/**
 * Class validator for check commit message format
 *
 * @package PreCommit\Validator
 */
class Jira implements InterfaceFilter
{
    /**
     * Issue adapter
     *
     * @var Issue\AdapterInterface
     */
    protected $_issue;

    /**
     * Filter commit message
     *
     * @param string $inputMessage
     * @param string $file
     * @return string
     */
    public function filter($inputMessage, $file = null)
    {
        list($mainLine, $comment) = $this->_explodeMessage($inputMessage);

        //interpret first row to get summary
        $interpretResult = $this->_interpretShortMessage($mainLine);
        if (!$interpretResult) {
            return $inputMessage;
        }
        list($verb, $issueKey, $commentInline) = $interpretResult;

        $comment = $this->_mergeComment($comment, $commentInline);
        $comment = $this->_addHyphen($comment);

        $this->_initIssue($issueKey);
        if (!$this->_getIssue()) {
            //ignore when could not get an issue to build a full message
            return $inputMessage;
        }

        $full = $this->_getFormattedMessage($verb, $comment);
        return $comment ? ($full . "\n" . $comment) : $full;
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
        $generalType = $this->_getIssueGeneralType();
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
        $map = $this->_getShortVerbsMap();
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
    protected function _getShortVerbsMap()
    {
        return (array)$this->_getConfig()->getNodeArray('filters/ShortCommitMsg/issue/short_verb');
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
    protected function _normalizeIssueKey($issueNo)
    {
        if ((string)(int)$issueNo === $issueNo) {
            $project = $this->_getConfig()->getNode('jira/project');
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
        $verbs = array_keys($this->_getShortVerbsMap());
        $verbs = implode('|', $verbs);
        $verbs = "($verbs)";
        preg_match("/^($verbs )?(([A-Z0-9]+[-])?[0-9]+)( [^\n]+)?/", trim($message), $m);
        if (!$m) {
            return false;
        }
        //skip first match
        array_shift($m);
        array_shift($m);

        $commitVerb  = trim(array_shift($m));
        $issueKey    = $this->_normalizeIssueKey(array_shift($m));
        $userMessage = null;
        if ($m) {
            //skip empty
            array_shift($m);
            $userMessage = trim(array_shift($m));
        }
        return array($commitVerb, $issueKey, $userMessage);
    }

    /**
     * Get issue general type
     *
     * @return array
     */
    protected function _getIssueGeneralType()
    {
        $issueType = $this->_issue->getType();
        $issueType = preg_replace('/[^A-z]/', '_', $issueType); //normalize name
        $xpath     = 'filters/ShortCommitMsg/issue/tracker/' . $this->_getTrackerName() . '/type/' . $issueType;
        return $this->_getConfig()->getNode($xpath);
        //throw new Exception("Invalid type for config node: '$xpath'.");
    }

    /**
     * Get tracker name
     *
     * @return string
     */
    protected function _getTrackerName()
    {
        return (string)$this->_getConfig()->getNode('tracker_name');
    }

    /**
     * Get default short verb by general issue type
     *
     * @param string $generalType
     * @return array|null
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
     * Explode message
     *
     * @param string $inputMessage
     * @return array
     */
    protected function _explodeMessage($inputMessage)
    {
        $arr         = explode("\n", $inputMessage);
        $firstLine   = array_shift($arr);
        $description = trim(str_replace($firstLine, '', $inputMessage));
        return array($firstLine, $description);
    }

    /**
     * Add "-" to comment row
     *
     * @param string $comment
     * @return string
     */
    protected function _addHyphen($comment)
    {
        if ($comment && 0 !== strpos(trim($comment), '-')) {
            $comment = ' - ' . $comment;
        }
        return $comment;
    }

    /**
     * Get formatted message
     *
     * @param string $verb
     * @return string
     */
    protected function _getFormattedMessage($verb)
    {
        return "{$this->_getVerb($verb)} {$this->_getIssue()->getKey()}: {$this->_issue->getSummary()}";
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
}
