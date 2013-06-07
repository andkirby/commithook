<?php
namespace PreCommit\Validator;

/**
 * Class CodingStandard validator
 *
 * @package PreCommit\Validator
 */
class RedundantCode extends AbstractValidator
{
    /**#@+
     * Error codes
     */
    const CODE_IS_NULL = 'isNullFunction';
    /**#@-*/

    /**
     * Error messages
     *
     * @var array
     */
    protected $_errorMessages = array(
        self::CODE_IS_NULL => 'Redundant usage is_null() function. Use null === $a construction. Original line: %value%',
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
        $parsedArr   = CodingStandard::splitContent($content);
        foreach ($parsedArr as $line => $str) {
            if (!$str) {
                //skip empty line
                continue;
            }
            $currentString = trim($originalArr[$line - 1]);
            //find is_null() function
            if (false !== strpos($str, 'is_null(')) {
                $this->_addError($file, self::CODE_IS_NULL, $currentString, $line);
            }
        }

        return array() == $this->_errorCollector->getErrors();
    }
}
