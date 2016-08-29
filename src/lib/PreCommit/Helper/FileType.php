<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */

namespace PreCommit\Helper;

use PreCommit\Config;

/**
 * This class can get info about file types for validation
 *
 * @package PreCommit\Helper
 */
class FileType
{
    /**
     * XML path to file types
     */
    const XPATH_FILE_TYPES = 'hooks/pre-commit/filetype/*';

    /**
     * XML path to file types for "before/after" placeholders
     */
    const XPATH_FILE_TYPES_ALL = 'hooks/pre-commit/filetype_for_all/*';

    /**
     * Get pre-commit file types
     *
     * @param bool $all Get file type for "before/after" as well
     * @return array
     */
    public function getFileTypes($all = true)
    {
        $base = array_keys($this->getConfig()->getNodesExpr(self::XPATH_FILE_TYPES));

        /**
         * Remove after/before keys
         */
        foreach (['after_all', 'before_all', 'before_all_original'] as $name) {
            $key = array_search($name, $base);
            if (false !== $key) {
                unset($base[$key]);
            }
        }

        if ($all) {
            $base = array_merge(
                $base,
                array_values($this->getConfig()->getNodesExpr(self::XPATH_FILE_TYPES))
            );
        }

        return $base;
    }

    /**
     * Get config model
     *
     * @return Config
     */
    protected function getConfig()
    {
        return Config::getInstance();
    }
}
