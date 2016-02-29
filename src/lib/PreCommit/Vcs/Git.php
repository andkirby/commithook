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
            //@startSkipCommitHooks
            $this->affectedFiles = array_filter(
                explode("\n", `git diff --cached --name-only --diff-filter=ACM`)
            );
            //@finishSkipCommitHooks
        }

        return $this->affectedFiles;
    }

    /**
     * Set affected files
     *
     * @param array $files
     * @return string
     */
    public function setAffectedFiles(array $files = null)
    {
        return $this->affectedFiles = $files;
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
        `git add $path`;

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
     * Get commit message file
     *
     * @return string
     */
    protected function getCommitMessageFile()
    {
        return $this->getCodePath().DIRECTORY_SEPARATOR.'.git'.DIRECTORY_SEPARATOR.'COMMIT_EDITMSG';
    }

    /**
     * Get path to project
     *
     * @return string
     */
    public function getCodePath()
    {
        //@startSkipCommitHooks
        return trim(`git rev-parse --show-toplevel`);
        //@finishSkipCommitHooks
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
        $mergeFile = $this->getCodePath().DIRECTORY_SEPARATOR.'.git'.DIRECTORY_SEPARATOR.'MERGE_HEAD';

        return file_exists($mergeFile);
    }
}
