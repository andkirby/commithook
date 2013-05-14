<?php
namespace PreCommit\Filter;

/**
 * Content filter for skipped code for validation
 *
 * @package PreCommit\Filter
 */
class SkipContent implements InterfaceFilter
{
    /**#@+
     * Skip tags
     *
     * For skip code add similar:
     * //@startSkipCommitHooks
     * $my->bad()->code();
     * //@finishSkipCommitHooks
     */
    const SKIP_TAG_START  = 'startSkipCommitHooks';
    const SKIP_TAG_FINISH = 'finishSkipCommitHooks';
    /**#@-*/

    /**
     * Filter skipped code blocks
     *
     * @param string $content
     * @param string|null $file
     * @return bool
     */
    public function filter($content, $file = null)
    {
        return preg_replace(
            '/(\s*\/\/@' . self::SKIP_TAG_START . ')([\S\s])*?(\/\/@' . self::SKIP_TAG_FINISH . ')/',
            '//replaced code because skipped validation', $content
        );
    }
}
