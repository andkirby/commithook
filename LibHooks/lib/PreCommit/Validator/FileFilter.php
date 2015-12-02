<?php
namespace PreCommit\Validator;

use PreCommit\Config;

/**
 * Class FileFilter validator
 *
 * @package PreCommit\Validator
 */
class FileFilter extends AbstractValidator
{
    /**#@+
     * XML path to config
     */
    const XPATH_SKIP_PATHS           = 'validators/FileFilter/filter/skip/paths/path';

    const XPATH_SKIP_FILES           = 'validators/FileFilter/filter/skip/files/file';

    const XPATH_SKIP_FILE_EXTENSIONS = 'validators/FileFilter/filter/skip/extensions';

    const XPATH_PROTECT_PATHS        = 'validators/FileFilter/filter/protect/paths/path';

    const XPATH_PROTECT_FILES        = 'validators/FileFilter/filter/protect/files/file';

    const XPATH_ALLOW_PATHS          = 'validators/FileFilter/filter/allow/paths/path';

    const XPATH_ALLOW_FILES          = 'validators/FileFilter/filter/allow/files/file';

    /**#@-*/

    /**#@+
     * Error codes
     */
    const PROTECTED_PATH = 'protectedPath';

    const PROTECTED_FILE = 'protectedFile';

    /**#@-*/

    /**
     * Error messages
     *
     * @var array
     */
    protected $errorMessages
        = array(
            self::PROTECTED_PATH => 'This file cannot be updated or added because it located in the protected path "%value%".',
            self::PROTECTED_FILE => 'This file cannot be updated or added because it protected.',
        );

    /**
     * Get ability to process file
     *
     * @param string $content
     * @param string $file
     * @return bool
     */
    public function validate($content, $file)
    {
        if (!$file) {
            return false;
        }

        if ($this->_isFileAllowed($file)) {
            //file is allowed to edit, no need to check protection
            return !$this->_isFileSkipped($file);
        }

        return !$this->_isFileProtected($file) && !$this->_isFileSkipped($file);
    }

    /**
     * Check file from ignore list
     *
     * @param $file
     * @return bool
     */
    protected function _isFileAllowed($file)
    {
        if ($this->_isFileAllowedByPath($file)) {
            return true;
        }
        $list = Config::getInstance()->getMultiNode(self::XPATH_ALLOW_FILES);
        foreach ($list as $item) {
            if (strpos($file, $item) === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check file from skip list
     *
     * @param string $file
     * @return bool
     */
    protected function _isFileSkipped($file)
    {
        if ($this->_isFileSkippedByPath($file)) {
            return true;
        }

        //check extension in skip list
        $listExtensions = Config::getInstance()->getNodeArray(self::XPATH_SKIP_FILE_EXTENSIONS);
        $fileExt        = pathinfo($file, PATHINFO_EXTENSION);
        if (in_array($fileExt, $listExtensions)) {
            return true;
        }

        //check file path in skip list
        $list = Config::getInstance()->getMultiNode(self::XPATH_SKIP_FILES);
        foreach ($list as $item) {
            $item = (string) $item;
            if (strpos($file, $item) === 0) {
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
    protected function _isFileProtected($file)
    {
        if ($this->_isFileProtectedByPath($file)) {
            return true;
        }
        $list = Config::getInstance()->getMultiNode(self::XPATH_PROTECT_FILES);
        foreach ($list as $item) {
            if (strpos($file, $item) === 0) {
                $this->_addError($file, self::PROTECTED_FILE, $item);

                return true;
            }
        }

        return false;
    }

    /**
     * Check if file in allowed path
     *
     * @param $file
     * @return bool
     */
    protected function _isFileAllowedByPath($file)
    {
        $list = Config::getInstance()->getMultiNode(self::XPATH_ALLOW_PATHS);
        foreach ($list as $item) {
            if (strpos($file, (string) $item) === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check file in the skip paths list
     *
     * @param string $file
     * @return bool
     */
    protected function _isFileSkippedByPath($file)
    {
        $list = Config::getInstance()->getMultiNode(self::XPATH_SKIP_PATHS);
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
    protected function _isFileProtectedByPath($file)
    {
        $list = Config::getInstance()->getMultiNode(self::XPATH_PROTECT_PATHS);
        foreach ($list as $item) {
            if (strpos($file, (string) $item) === 0) {
                $this->_addError($file, self::PROTECTED_PATH, $item);

                return true;
            }
        }

        return false;
    }
}
