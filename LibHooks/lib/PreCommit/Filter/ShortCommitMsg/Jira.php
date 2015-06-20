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
        $inputMessage = trim($inputMessage);
        $arr = explode("\n", $inputMessage);
        $first = array_shift($arr);

        //improve extra description row
        $secondOrig = array_shift($arr);
        if ($secondOrig && 0 !== strpos(trim($secondOrig), '-')) {
            $inputMessage = str_replace($secondOrig, ' - ' . trim($secondOrig), $inputMessage);
        }

        //interpret first row to get summary
        $interpretResult = $this->_interpretShortMessage($first);
        if (!$interpretResult) {
            return $inputMessage;
        }
        list($verb, $issueKey) = $interpretResult;

        $this->_issue = Issue::factory($issueKey);
        if (!$this->_issue) {
            //could not get an issue
            return $inputMessage;
        }

        $full = "{$this->_getVerb($verb)} {$this->_issue->getKey()}: {$this->_issue->getSummary()}";
        return str_replace($first, $full, $inputMessage);
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
        preg_match("/^($verbs )?(([A-Z0-9]+-)?[0-9]+)/", $message, $m);
        if (!$m) {
            return false;
        }
        //skip first match
        array_shift($m);
        array_shift($m);

        $commitVerb = trim(array_shift($m));
        $issueKey = $this->_normalizeIssueKey(array_shift($m));
        return array($commitVerb, $issueKey);
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
}
