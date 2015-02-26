<?php
namespace PreCommit\Vcs;

/**
 * Class for VCS adapter interface
 */
interface AdapterInterface
{
    /**
     * Get path to project
     *
     * @return string
     */
    public function getCodePath();

    /**
     * Get affected files
     *
     * @return string
     */
    public function getAffectedFiles();

    /**
     * Get commit message
     *
     * @return string
     */
    public function getCommitMessage();

    /**
     * Set commit message
     *
     * @param string $message
     * @return string
     */
    public function setCommitMessage($message);

    /**
     * Get merge status
     *
     * @return bool
     */
    public function isMergeInProgress();
}
