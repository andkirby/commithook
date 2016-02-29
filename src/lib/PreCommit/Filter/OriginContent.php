<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
namespace PreCommit\Filter;

use PreCommit\Validator\Helper\LineFinder;

/**
 * Content filter for skipped code for validation
 *
 * @package PreCommit\Filter
 */
class OriginContent implements FilterInterface
{
    /**
     * Filter skipped code blocks
     *
     * @param string      $content
     * @param string|null $file
     * @return bool
     */
    public function filter($content, $file = null)
    {
        /**
         * Set original content for finding right line numbers
         *
         * @todo Remove hack for saving original content
         */
        LineFinder::setOriginContent($content);

        return $content;
    }
}
