<?php
namespace PreCommit\Validator;

use PreCommit\Exception;

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
    protected $_errorMessages
        = array(
            self::CODE_IS_NULL   => 'Redundant usage is_null() function. Use null === $a construction. Original line: %value%',
            self::JS_CONSOLE     => 'Redundant usage JS console.log() function. Original line: %value%',
            self::DEBUG_QQQ      => 'Redundant usage qqq() debug function. Original line: %value%',
            self::DEBUG_VAR_DUMP => 'Redundant usage var_dump() debug function. Original line: %value%',
        );

    /**
     * File types list for each redundant code
     *
     * @var array
     */
    protected $_fileTypes
        = array(
            self::CODE_IS_NULL   => array('php', 'phtml'),
            self::JS_CONSOLE     => array('php', 'phtml', 'js'),
            self::DEBUG_QQQ      => array('php', 'phtml'),
            self::DEBUG_VAR_DUMP => array('php', 'phtml'),
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
        $ext = pathinfo($file, PATHINFO_EXTENSION);

        if (!in_array($ext, array('js', 'php', 'phtml'))) {
            return true;
        }

        $content     = preg_replace('/\x0A\x0D|\x0D\x0A|\x0A|\x0D/', "\n", $content);
        $originalArr = preg_split('/\x0A\x0D|\x0D\x0A|\x0A|\x0D/', $content);
        $parsedArr   = CodingStandard::splitContent($content);
        foreach ($parsedArr as $line => $str) {
            if (!$str) {
                //skip empty line
                continue;
            }
            $currentString = trim($originalArr[$line - 1]);

            //find console.log()
            if ($this->_canCheckType($ext, self::JS_CONSOLE) && false !== strpos($str, 'console.log(')) {
                $this->_addError($file, self::JS_CONSOLE, $currentString, $line);
            }
            //find is_null() function
            if ($this->_canCheckType($ext, self::CODE_IS_NULL) && false !== strpos($str, 'is_null(')) {
                $this->_addError($file, self::CODE_IS_NULL, $currentString, $line);
            }
            //find qqq()
            if ($this->_canCheckType($ext, self::DEBUG_QQQ) && false !== strpos($str, 'qqq')) {
                $this->_addError($file, self::DEBUG_QQQ, $currentString, $line);
            }
            //find var_dump()
            if ($this->_canCheckType($ext, self::DEBUG_VAR_DUMP) && false !== strpos($str, 'var_dump(')) {
                $this->_addError($file, self::DEBUG_VAR_DUMP, $currentString, $line);
            }
        }

        return !$this->_errorCollector->hasErrors();
    }

    /**
     * Check that file extension added for specific checking
     *
     * @param string $extension
     * @param string $code
     * @return bool
     * @throws \PreCommit\Exception
     */
    protected function _canCheckType($extension, $code)
    {
        if (!isset($this->_fileTypes[$code])) {
            throw new Exception("Unknown code $code.");
        }

        return in_array($extension, $this->_fileTypes[$code]);
    }
}
