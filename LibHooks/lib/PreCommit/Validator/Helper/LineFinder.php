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
        $targetLength = strlen($find);
        str_replace("\n", '', $find, $lineShift);
        while ($position = strpos($content, $find, $offset)) {
            str_replace("\n", '', substr($content, 0, $position), $line);
            $line = $line + 1 + $lineShift;
            if ($once) {
                return (int)$line;
            }
            $lines[] = $line;
            $offset = $position + $targetLength;
        }
        return $lines;
    }
}
