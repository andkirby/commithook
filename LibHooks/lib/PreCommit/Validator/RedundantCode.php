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
    const CODE_IS_NULL   = 'isNullFunction';
    const JS_CONSOLE     = 'jsConsoleFunction';
    const DEBUG_QQQ      = 'qqqDebugFunction';
    const DEBUG_VAR_DUMP = 'varDumpDebugFunction';
    /**#@-*/

    /**
     * Error messages
     *
     * @var array
     */
    protected $_errorMessages = array(
        self::CODE_IS_NULL => 'Redundant usage is_null() function. Use null === $a construction. Original line: %value%',
        self::JS_CONSOLE   => 'Redundant usage JS console.log() function. Original line: %value%',
        self::DEBUG_QQQ    => 'Redundant usage qqq() debug function. Original line: %value%',
        self::DEBUG_VAR_DUMP => 'Redundant usage var_dump() debug function. Original line: %value%',
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
            //find console.log()
            if (false !== strpos($str, 'console.log(')) {
                $this->_addError($file, self::JS_CONSOLE, $currentString, $line);
            }
            //find qqq()
            if (false !== strpos($str, 'qqq')) {
                $this->_addError($file, self::DEBUG_QQQ, $currentString, $line);
            }
            //find var_dump()
            if (false !== strpos($str, 'var_dump(')) {
                $this->_addError($file, self::DEBUG_VAR_DUMP, $currentString, $line);
            }
        }

        return array() == $this->_errorCollector->getErrors();
    }
}
