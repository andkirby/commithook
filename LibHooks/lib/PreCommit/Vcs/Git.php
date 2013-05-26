<?php
namespace PreCommit\Vcs;

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
}
