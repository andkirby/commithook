<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
/**
 * Created by PhpStorm.
 * User: a.roslik
 * Date: 11/2/16 002
 * Time: 6:06 PM
 */

namespace PreCommit\Filter;

/**
 * Class HereNowdoc filter
 *
 * @package AndKirby\PreCommit\Filter
 */
class HereNowdoc
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
        foreach ($this->findTextBlockTags($content) as $tag) {
            $content = $this->cut($tag, $content);
        }

        return $content;
    }

    /**
     * Cut text code block
     *
     * @param string $tag
     * @param string $content
     * @return string
     */
    protected function cut($tag, $content)
    {
        return preg_replace(
            '/(=[ ]*)<<<[\'"]?'.$tag.'[\'"]?\r?\n(\r|\n|.)*?'.$tag.';/',
            '$1\'\'; //replaced code because skipped validation',
            $content
        );
    }

    /**
     * Find text block tags
     *
     * @param string $content
     * @return array
     */
    protected function findTextBlockTags($content)
    {
        //find blocks
        preg_match_all('/=[ ]*<<<[\'"]?([A-z0-9]+)[\'"]?\r?\n/', $content, $matches);

        return empty($matches[1]) ? [] : array_unique($matches[1]);
    }
}
