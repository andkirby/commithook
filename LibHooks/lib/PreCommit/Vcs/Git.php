<?php
namespace PreCommit\Vcs;

use PreCommit\Exception;

/**
 * Class for VCS adapter Git
 */
class Git implements AdapterInterface
{
    /**
     * Get path to project
     *
     * @return string
     */
    public function getCodePath()
    {
        return trim(`git rev-parse --show-toplevel`);
    }

    /**
     * Get affected files
     *
     * @return string
     */
    public function getAffectedFiles()
    {
        if (defined('TEST_MODE') && TEST_MODE) {
            return array_filter(explode("\n", `git ls-files -m`));
        }
        return array_filter(explode("\n", `git diff --cached --name-only --diff-filter=ACM`));
    }

    /**
     * Get inner text of commit message file
     *
     * @return string
     * @throws \PreCommit\Exception
     */
    public function getCommitMessage()
    {
        $file = $this->_getCommitMessageFile();
        if (!file_exists($file)) {
            throw new Exception("Commit message file '$file' not found.");
        }
        return file_get_contents($file);
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
        $file = $this->_getCommitMessageFile();
        if (false === file_put_contents($file, $message)) {
            throw new Exception("Commit message file '$file' cannot be updated.");
        }
        return $this;
    }

    /**
     * Get commit message file
     *
     * @return string
     */
    protected function _getCommitMessageFile()
    {
        return $this->getCodePath() . DIRECTORY_SEPARATOR . '.git'
        . DIRECTORY_SEPARATOR . 'COMMIT_EDITMSG';
    }

    /**
     * Get inner text of commit message file
     *
     * @return string
     */
    public function isMergeInProgress()
    {
        $mergeFile = $this->getCodePath() . DIRECTORY_SEPARATOR . '.git' . DIRECTORY_SEPARATOR . 'MERGE_HEAD';
        return file_exists($mergeFile);
    }
}
