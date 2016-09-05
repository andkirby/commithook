<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
namespace PreCommit\Validator;

use PreCommit\Config;
use PreCommit\Helper\PathMatch;

/**
 * Class FileFilter validator
 *
 * @package PreCommit\Validator
 */
class FileFilter extends AbstractValidator
{
    /**
     * XML path to skipped extensions
     */
    const XPATH_SKIP_FILE_EXTENSIONS = 'validators/FileFilter/filter/skip/extensions';

    /**#@+
     * XML path to config
     */
    const XPATH_SKIP_PATH    = 'validators/FileFilter/filter/skip/path';
    const XPATH_PROTECT_PATH = 'validators/FileFilter/filter/protect/path';
    const XPATH_ALLOW_PATH   = 'validators/FileFilter/filter/allow/path';
    /**#@-*/

    /**
     * XPath to flag of allowing path default to process files to commit
     */
    const XPATH_ALLOW_BY_DEFAULT = 'validators/FileFilter/allowed_by_default';

    /**#@+
     * Error codes
     */
    const PROTECTED_PATH = 'protectedPath';
    const PROTECTED_ALL  = 'protectedAll';
    /**#@-*/

    /**
     * Error messages
     *
     * @var array
     */
    protected $errorMessages = [
        self::PROTECTED_PATH => 'This file cannot be updated or added because it located in the protected path "%value%".',
        self::PROTECTED_ALL  => 'This file cannot be updated or added because this path protected by default.',
    ];

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

        $matchSkip = new PathMatch();
        $matchSkip->setAllowed($this->getPathList(self::XPATH_SKIP_PATH));
        if ($matchSkip->test($file) || $this->isExtensionSkipped($file)) {
            /**
             * Return FALSE as file is not allowed to validate, ie should be skipped
             *
             * @see \PreCommit\Processor\PreCommit::process()
             */
            return false;
        }

        $match = new PathMatch();
        $match->setAllowedByDefault($this->getValue(self::XPATH_ALLOW_BY_DEFAULT));
        $match->setAllowed($this->getPathList(self::XPATH_ALLOW_PATH));
        $match->setProtected($this->getPathList(self::XPATH_PROTECT_PATH));

        if ($match->test($file)) {
            return true;
        }

        if (!$match->getMatch() && !$match->getAllowedByDefault()) {
            $this->addError($file, self::PROTECTED_ALL);
        } else {
            $this->addError($file, self::PROTECTED_PATH, $match->getMatch());
        }

        return false;
    }

    /**
     * Get path list
     *
     * @param string $xpath
     * @return array|null
     */
    protected function getPathList($xpath)
    {
        return Config::getInstance()->getNodeArray($xpath);
    }

    /**
     * Check if extension is skipped
     *
     * @param string $file
     * @return bool
     */
    protected function isExtensionSkipped($file)
    {
        //check extension in skip list
        $listExtensions = $this->getPathList(self::XPATH_SKIP_FILE_EXTENSIONS);
        $fileExt        = pathinfo($file, PATHINFO_EXTENSION);

        return in_array($fileExt, $listExtensions);
    }

    /**
     * Get xpath value
     *
     * @param string $xpath
     * @return array|null
     */
    protected function getValue($xpath)
    {
        return Config::getInstance()->getNode($xpath);
    }
}
