<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
namespace PreCommit\Validator\Magento;

use PreCommit\Validator\CodingStandard;

/**
 * Class CodingStandard validator
 *
 * @package PreCommit\Validator
 */
class MageExceptionThrow extends CodingStandard
{
    /**#@+
     * Error codes
     */
    const CODE_PHP_DEPRECATED_THROW_EXCEPTION = 'deprecatedUsingMageThrowException';
    /**#@-*/

    /**
     * Error messages
     *
     * @var array
     */
    protected $errorMessages
        = array(
            self::CODE_PHP_DEPRECATED_THROW_EXCEPTION => 'Used deprecated method Mage::throwException(). Use: throw new Your_Module_Exception(\'Your message.\')',
        );

    /**
     * Validate content
     *
     * @param string $content
     * @param string $file
     * @return bool
     */
    public function validate($content, $file)
    {
        $originalArr = preg_split('/\x0A\x0D|\x0D\x0A|\x0A|\x0D/', $content);
        $parsedArr   = $this->splitContent($content);
        foreach ($parsedArr as $line => $str) {
            if (!isset($originalArr[$line - 1])) {
                //skip if line not exists
                //TODO investigate this case
                continue;
            }
            $currentString = trim($originalArr[$line - 1]);
            //check using Mage::throwException();
            if (false !== strpos($str, 'Mage::throwException(')) {
                $this->addError($file, self::CODE_PHP_DEPRECATED_THROW_EXCEPTION, $currentString, $line);
            }
        }

        return !$this->errorCollector->hasErrors();
    }
}
