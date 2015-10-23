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

    /**
     * Get original issue type
     *
     * @return $this
     */
    public function getOriginalType();

    /**
     * Get issue status
     *
     * @return string
     */
    public function getStatus();

    /**
     * Ignore issue on failed validation
     *
     * It should be removed from cache at least to make new request in future.
     *
     * @return string
     */
    public function ignoreIssue();
}
