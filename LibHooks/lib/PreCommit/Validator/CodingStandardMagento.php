<?php
namespace PreCommit\Validator;

/**
 * Class CodingStandard validator
 *
 * @package PreCommit\Validator
 */
class CodingStandardMagento extends CodingStandard
{
    /**#@+
     * Error codes
     */
    const CODE_PHP_DEPRECATED_THROW_EXCEPTION                    = 'deprecatedUsingMageThrowException';
    /**#@-*/

    /**
     * Error messages
     *
     * @var array
     */
    protected $_errorMessages = array(
        self::CODE_PHP_DEPRECATED_THROW_EXCEPTION => 'Used deprecated method Mage::throwException(). Use: throw new Mage_Core_Exception("Translated message.")',
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
        $parsedArr = $this->splitContent($content);
        foreach ($parsedArr as $line => $str) {
            $currentString = trim($originalArr[$line - 1]);
            //check using Mage::throwException();
            if (false !== strpos($str, 'Mage::throwException(')) {
                $this->_addError($file, self::CODE_PHP_DEPRECATED_THROW_EXCEPTION, $currentString, $line);
            }
        }

        return array() == $this->_errorCollector->getErrors();
    }
}
