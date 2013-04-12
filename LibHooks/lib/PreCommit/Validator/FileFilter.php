<?php
namespace PreCommit\Validator;
use \PreCommit\Config;

/**
 * Class FileFilter validator
 *
 * @package PreCommit\Validator
 */
class FileFilter extends AbstractValidator
{
    /**
     * Overridden constructor
     */
    public function __construct()
    {

    }

    /**
     * Get ability to process file
     *
     * @param string $content
     * @param string $file
     * @return bool
     */
    public function validate($content, $file)
    {
        return $file && !$this->_isFileExcludedByPath($file) && !$this->_isFileIgnored($file);
    }

    /**
     * Check file in the excluded paths
     *
     * @param string $file
     * @return bool
     */
    protected function _isFileExcludedByPath($file)
    {
        $list = Config::getInstance()->getNode('processing/filter/paths/blacklist/path', true);
        foreach ($list as $item) {
            if (strpos($file, (string) $item) === 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check file from ignore list
     *
     * @param $file
     * @return bool
     */
    protected function _isFileIgnored($file)
    {
        $list = Config::getInstance()->getNode('processing/filter/files/blacklist/file', true);
        foreach ($list as $item) {
            $item = (string) $item;
            if (strpos($file, $item) !== false) {
                return true;
            }
        }
        return false;
    }
}
