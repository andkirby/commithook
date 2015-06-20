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
     * Set issue key
     *
     * @param string $issueKey
     */
    public function __construct($issueKey);

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
     * Get issue type
     *
     * @return $this
     */
    public function getType();
}
