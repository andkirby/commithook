<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
namespace PreCommit\Issue;

use PreCommit\Config;
use PreCommit\Exception;
use PreCommit\Jira\Api;

/**
 * Abstract adapter class
 *
 * @package PreCommit\Issue
 */
abstract class AbstractAdapter implements AdapterInterface
{
    /**
     * Message interpreting type
     *
     * @var string
     */
    protected $type;

    /**
     * Set type
     *
     * @param string $issueKey
     * @throws \PreCommit\Exception
     */
    public function __construct($issueKey)
    {
        $this->type = $this->getConfig()->getNode('hooks/commit-msg/message/type');
        if (!$this->type) {
            throw new Exception('Type is not set.');
        }
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
     * Get issue general type
     *
     * @return array
     */
    public function getType()
    {
        $issueType = $this->normalizeName($this->getOriginalType());

        return $this->getConfig()->getNode(
            'hooks/commit-msg/message/issue/type/tracker/'.$this->getTrackerType().'/'.$this->type.'/'.$issueType
        )
            ?: $this->getConfig()->getNode(
                'hooks/commit-msg/message/issue/type/tracker/'.$this->getTrackerType().'/default/'.$issueType
            );
    }

    /**
     * Normalize name
     *
     * @param string $name
     * @return string
     */
    protected function normalizeName($name)
    {
        return preg_replace('/[^A-z]/', '_', $name);
    }

    /**
     * Get tracker name
     *
     * @return string
     */
    protected function getTrackerType()
    {
        return (string) $this->getConfig()->getNode('tracker/type');
    }
}
