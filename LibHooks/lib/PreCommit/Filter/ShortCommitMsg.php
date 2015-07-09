<?php
namespace PreCommit\Filter;

/**
 * Class ShortCommitMsg filter
 *
 * @package PreCommit\Filter
 */
class ShortCommitMsg implements InterfaceFilter
{
    /**
     * Filter short commit message
     *
     * @param string $content
     * @param string|null   $file
     * @return string
     */
    public function filter($content, $file = null)
    {
        //JIRA is the one issue tracker so far
        //TODO implement factory loading
        $jira = new ShortCommitMsg\Jira();
        return $jira->filter($content, $file);
    }
}
