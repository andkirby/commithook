<?php
namespace PreCommit\Validator\Helper;

/**
 * Class LineFinder
 *
 * Helps for to find input strings in content
 *
 * @package PreCommit\Validator\Helper
 */
class LineFinder
{
    /**
     * Find line
     *
     * @param string $find
     * @param string $content
     * @param bool   $once
     * @return array|int
     */
    public static function findLines($find, $content, $once = false)
    {
        $offset = 0;
        $lines = array();

        //get length to set offset for next iteration
        $targetLength = strlen($find);

        //find line endings in a finding string
        str_replace("\n", '', $find, $lineShift);

        while ($position = strpos($content, $find, $offset)) {
            //get line position
            str_replace("\n", '', substr($content, 0, $position), $line);
            $line = $line + 1 + $lineShift;

            if ($once) {
                //if once - return only first match
                return (int)$line;
            }

            $lines[] = $line;

            //set offset for next iteration
            $offset = $position + $targetLength;
        }
        return $lines;
    }
}
