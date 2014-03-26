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
        $file = $this->getCodePath() . DIRECTORY_SEPARATOR . '.git' . DIRECTORY_SEPARATOR . 'COMMIT_EDITMSG';
        if (!file_exists($file)) {
            throw new Exception("Commit message file '$file' not found.");
        }
        return file_get_contents($file);
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
