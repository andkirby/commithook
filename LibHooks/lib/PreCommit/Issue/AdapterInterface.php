<?php
namespace PreCommit\Issue;

/**
 * Interface AdapterInterface
 *
 * @package PreCommit\Issue
 */
interface AdapterInterface
{
    /**#@+
     * Issue types
     */
    const TYPE_BUG = 'bug';
    const TYPE_TASK = 'task';
    /**#@-*/

    /**
     * Get issue summary
     *
     * @return $this
     */
    public function getSummary();

    /**
     * Get issue summary
     *
     * @return $this
     */
    public function getKey();

    /**
     * Get issue summary
     *
     * @return $this
     */
    public function getType();

    /**
     * Check type "bug"
     *
     * @return bool
     */
    public function isBug();

    /**
     * Check type "task"
     *
     * @return bool
     */
    public function isTask();
}
