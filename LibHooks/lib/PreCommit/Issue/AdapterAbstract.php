<?php
namespace PreCommit\Issue;

use PreCommit\Config;
use PreCommit\Exception;
use PreCommit\Jira\Api;

/**
 * Abstract adapter class
 *
 * @package PreCommit\Issue
 */
abstract class AdapterAbstract implements AdapterInterface
{
    /**
     * Message interpreting type
     *
     * @var string
     */
    protected $_type;

    /**
     * Set type
     *
     * @param string $issueKey
     * @throws \PreCommit\Exception
     */
    public function __construct($issueKey)
    {
        $this->_type = $this->_getConfig()->getNode('hooks/commit-msg/message/type');
        if (!$this->_type) {
            throw new Exception('Type is not set.');
        }
    }

    /**
     * Get internal issue type
     *
     * @return string
     */
    abstract public function getIssueType();

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
     * Get issue general type
     *
     * @return array
     */
    public function getType()
    {
        $issueType = preg_replace('/[^A-z]/', '_', $this->getIssueType()); //normalize name
        $xpath     = 'hooks/commit-msg/message/issue/type/tracker/'
                     . $this->_getTrackerName() . '/' . $this->_type . '/' . $issueType;
        return $this->_getConfig()->getNode($xpath);
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
}
