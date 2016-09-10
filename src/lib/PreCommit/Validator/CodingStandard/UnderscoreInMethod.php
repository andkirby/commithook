<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
namespace PreCommit\Validator\CodingStandard;

use PreCommit\Config;
use PreCommit\Validator\CodingStandard as CodingStandardValidator;

/**
 * Class CodingStandard validator
 *
 * @package PreCommit\Validator
 */
class UnderscoreInMethod extends CodingStandardValidator
{
    /**#@+
     * Skip tag for publicMethodNaming errors
     */
    const SKIP_TAG_PUBLIC_METHOD_NAMING = 'skipPublicMethodNaming';
    const SKIP_TAG_METHOD_NAMING        = 'skipCommitHookMethodNaming';
    /**#@-*/

    /**#@+
     * Error codes
     */
    const CODE_PHP_PROTECTED_METHOD_NAMING_INVALID               = 'protectedMethodNaming';
    const CODE_PHP_PROTECTED_METHOD_NAMING_INVALID_NO_UNDERSCORE = 'protectedMethodNamingNoUnderscore';
    /**#@-*/

    /**
     * Error messages
     *
     * @var array
     */
    protected $errorMessages
        = [
            self::CODE_PHP_PROTECTED_METHOD_NAMING_INVALID               => 'Protected or private method name should start with underscore and two small letters. Original line: %value%',
            self::CODE_PHP_PROTECTED_METHOD_NAMING_INVALID_NO_UNDERSCORE => 'Protected or private method name should start with underscore and two small letters. Original line: %value%',
        ];

    /**
     * Validate content
     *
     * @param string $content
     * @param string $file
     * @return bool
     */
    public function validate($content, $file)
    {
        $this->validateCodeStyleByLines($content, $file);

        return !$this->errorCollector->hasErrors();
    }

    /**
     * Validate code style by lines
     *
     * @param string $content
     * @param string $file
     * @return $this
     */
    protected function validateCodeStyleByLines($content, $file)
    {
        $originalArr = preg_split('/\x0A\x0D|\x0D\x0A|\x0A|\x0D/', $content);
        $parsedArr   = $this->splitContent($content);
        foreach ($parsedArr as $line => $str) {
            if (!$str) {
                //skip empty line
                continue;
            }

            if (!isset($originalArr[$line - 1])) {
                //skip not exit line
                //TODO investigate this case.
                continue;
            }

            $currentString = trim($originalArr[$line - 1]);

            //check function naming and scope
            if (strpos($str, ' function ') && preg_match('#^ {4}[a-z][a-z ]+#', $str, $matches)) {
                if (!$this->isSkipMethodNameValidation($content, $str)) {
                    if ($this->useUnderscoreInNonPublic()
                        && preg_match('/^\s*(static )?(protected|private) /', $str)
                        && !preg_match('/(protected|private) (static )?function _[a-z]{2}/', $str)
                    ) {
                        $this->addError($file, self::CODE_PHP_PROTECTED_METHOD_NAMING_INVALID, $currentString, $line);
                    } elseif (!$this->useUnderscoreInNonPublic()
                              && preg_match('/^\s*(static )?(protected|private) /', $str)
                              && !preg_match('/(protected|private) (static )?function [a-z]{2}/', $str)
                    ) {
                        $this->addError(
                            $file,
                            self::CODE_PHP_PROTECTED_METHOD_NAMING_INVALID_NO_UNDERSCORE,
                            $currentString,
                            $line
                        );
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Check skip validation public method
     *
     * @param string $content
     * @param string $str
     * @return bool
     */
    protected function isSkipMethodNameValidation($content, $str)
    {
        preg_match('/function ([A-z_]+)/', $str, $matches);
        if ($matches) {
            $funcName = $matches[1];
            $reg      = '~[ *]*\@('.self::SKIP_TAG_PUBLIC_METHOD_NAMING.'|'.self::SKIP_TAG_METHOD_NAMING.')\s+'.$funcName.'\n~';
            if (preg_match($reg, $content, $m)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get configuration of underscore using in non-public methods
     *
     * @return bool
     */
    protected function useUnderscoreInNonPublic()
    {
        // use old value for backward compatibility
        $old = Config::getInstance()
            ->getNode('validators/CodingStandard/underscore_in_non_public');
        if ($old) {
            return (bool) $old;
        }

        return (bool) Config::getInstance()
            ->getNode('validators/CodingStandard-UnderscoreInMethod/underscore_in_non_public');
    }
}
