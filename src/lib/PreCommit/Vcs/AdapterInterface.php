<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
namespace PreCommit\Vcs;

/**
 * Class for VCS adapter interface
 */
interface AdapterInterface
{
    /**
     * Get path to project
     *
     * @param string|null $dotGitDir Anti-pattern, please use it carefully.
     *                               This variable may have path to dot-git directory.
     *                               It's quiet useful for GIT submodules.
     * @return string
     */
    public function getCodePath($dotGitDir = null);

    /**
     * Get affected files
     *
     * @param array $files
     * @return string
     */
    public function setAffectedFiles(array $files = null);

    /**
     * Get affected files
     *
     * @return array
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
     * Add path to VCS
     *
     * @param string $path
     * @return $this
     */
    public function addPath($path);

    /**
     * Get merge status
     *
     * @return bool
     */
    public function isMergeInProgress();
}
