<?php
namespace PreCommit\Filter;

use PreCommit\Validator\Helper\LineFinder;

/**
 * Content filter for skipped code for validation
 *
 * @package PreCommit\Filter
 */
class SkipContent implements FilterInterface
{
    /**#@+
     * Skip tags
     *
     * For skip code add similar:
     * [slash][slash]@startSkipCommitHooks
     * $my->bad()->code();
     * [slash][slash]@finishSkipCommitHooks
     */
    const SKIP_TAG_START = 'startSkipCommitHooks';
    const SKIP_TAG_FINISH = 'finishSkipCommitHooks';
    /**#@-*/

    /**
     * Filter skipped code blocks
     *
     * @param string      $content
     * @param string|null $file
     * @return bool
     */
    public function filter($content, $file = null)
    {
        //TODO Remove hack for saving original content
        LineFinder::setOriginContent($content);

        return preg_replace(
            '/(\s*\/\/@'.self::SKIP_TAG_START.')([\S\s])*?(\/\/@'.self::SKIP_TAG_FINISH.')/',
            '//replaced code because skipped validation',
            $content
        );
    }
}
