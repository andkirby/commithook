<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
namespace PreCommit\Filter\CodeSniffer;

use PreCommit\Filter\FilterInterface;

/**
 * Content filter for skipped code for validation
 *
 * @package PreCommit\Filter
 */
class SkipContent implements FilterInterface
{
    /**#@+
     * Skip tags of CodeSniffer
     *
     * For skip code add similar:
     * [slash][slash] @codingStandardsIgnoreStart
     * $my->bad()->code();
     * [slash][slash] @codingStandardsIgnoreEnd
     */
    const SKIP_TAG_START  = 'codingStandardsIgnoreStart';
    const SKIP_TAG_FINISH = 'codingStandardsIgnoreEnd';
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
        return $this->cutCodeBlock($content);
    }

    /**
     * Cut skipping code block
     *
     * @param string $content
     * @return string
     */
    protected function cutCodeBlock($content)
    {
        return (string) preg_replace(
            '/(\s*\/\/ @'.self::SKIP_TAG_START.')([\S\s])*?(\/\/@'.self::SKIP_TAG_FINISH.')/',
            '//replaced code because skipped validation (CodeSniffer)',
            $content
        );
    }
}
