<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
namespace PreCommit\Vcs;

use PreCommit\Exception;

/**
 * Class for VCS adapter Git
 */
class Git implements AdapterInterface
{
    /**
     * Absolute path to GIT repo
     *
     * @var string
     */
    protected $codePath;

    /**
     * Affected files
     *
     * @var string
     */
    protected $affectedFiles;

    /**
     * Get affected files
     *
     * @return string
     */
    public function getAffectedFiles()
    {
        if (null === $this->affectedFiles) {
            $gitBin = GIT_BIN;
            //@startSkipCommitHooks
            $this->affectedFiles = array_filter(
                explode(
                    "\n",
                    str_replace('\\', '/', `$gitBin diff --cached --name-only --diff-filter=ACM`)
                )
            );
            //@finishSkipCommitHooks
        }

        return $this->affectedFiles;
    }

    /**
     * Set affected files
     *
     * @param array $files
     * @return $this
     */
    public function setAffectedFiles(array $files = null)
    {
        if ($files) {
            foreach ($files as &$path) {
                $path = str_replace('\\', '/', $path);
            }
        }

        $this->affectedFiles = $files;

        return $this;
    }

    /**
     * Add path to VCS
     *
     * @param string $path
     * @return $this
     * @throws \PreCommit\Exception
     */
    public function addPath($path)
    {
        if (!realpath($path)) {
            throw new Exception('Unknown path: '.$path);
        }
        $gitBin = GIT_BIN;
        `$gitBin add $path`;

        return $this;
    }

    /**
     * Get inner text of commit message file
     *
     * @return string
     * @throws \PreCommit\Exception
     */
    public function getCommitMessage()
    {
        $file = $this->getCommitMessageFile();
        if (!file_exists($file)) {
            throw new Exception("Commit message file '$file' not found.");
        }

        return file_get_contents($file);
    }

    /**
     * Get path to project
     *
     * @return string
     */
    public function getCodePath()
    {
        if (null === $this->codePath) {
            $gitBin = GIT_BIN;
            //@startSkipCommitHooks
            $this->codePath = trim(`$gitBin rev-parse --show-toplevel`);
            //@finishSkipCommitHooks
        }

        return $this->codePath;
    }

    /**
     * Get inner text of commit message file
     *
     * @param string $message
     * @return string
     * @throws \PreCommit\Exception
     */
    public function setCommitMessage($message)
    {
        $file = $this->getCommitMessageFile();
        if (false === file_put_contents($file, $message)) {
            throw new Exception("Commit message file '$file' cannot be updated.");
        }

        return $this;
    }

    /**
     * Get inner text of commit message file
     *
     * @return string
     */
    public function isMergeInProgress()
    {
        $mergeFile = $this->getDotGitDirectory().DIRECTORY_SEPARATOR.'MERGE_HEAD';

        return file_exists($mergeFile);
    }

    /**
     * Get commit message file
     *
     * @return string
     */
    public function getCommitMessageFile()
    {
        return $this->getDotGitDirectory().DIRECTORY_SEPARATOR.'COMMIT_EDITMSG';
    }

    /**
     * Get GIT directory (.git)
     *
     * @return string
     */
    public function getDotGitDirectory()
    {
        // @codingStandardsIgnoreStart
        return realpath(trim(`git -C {$this->getCodePath()} rev-parse --git-dir 2>&1`));
        // @codingStandardsIgnoreEnd
    }
}
