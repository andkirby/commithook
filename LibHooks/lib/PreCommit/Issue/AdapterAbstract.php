<?php
namespace PreCommit\Issue;

use PreCommit\Jira\Api;

/**
 * Abstract adapter class
 *
 * @package PreCommit\Issue
 */
abstract class AdapterAbstract implements AdapterInterface
{
    /**
     * Check issue type "bug"
     *
     * @return bool
     */
    public function isBug()
    {
        return self::TYPE_BUG === $this->getType();
    }

    /**
     * Check issue type "task"
     *
     * @return bool
     */
    public function isTask()
    {
        return self::TYPE_TASK === $this->getType();
    }
}
