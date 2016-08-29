<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
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
    const TYPE_BUG  = 'bug';
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
     * @return string
     */
    public function getSummary();

    /**
     * Get issue summary
     *
     * @return string
     */
    public function getKey();

    /**
     * Get issue type
     *
     * @return string
     */
    public function getType();

    /**
     * Get original issue type
     *
     * @return string
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
