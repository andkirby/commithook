<?php
namespace PreCommit\Validator;
use \PreCommit\Processor\ErrorCollector as Error;

/**
 * Class code style validator
 *
 * @package PreCommit\Validator
 */
class FileStyle extends AbstractValidator
{
    /**#@+
     * Error codes
     */
    const CODE_TAB_CHAR = 'tabCharacter';
    const CODE_WIN_LINE_BREAK = 'winLine';
    const CODE_FILE_BOM = 'fileBom';
    /**#@-*/

    /**
     * Error messages
     *
     * @var array
     */
    protected $_errorMessages = array(
        self::CODE_TAB_CHAR => 'Contains tab character. Total Amount of tabs: %value%.',
        self::CODE_WIN_LINE_BREAK => 'Windows line breaks found: %value%',
        self::CODE_FILE_BOM => 'File starts with BOM (http://en.wikipedia.org/wiki/Byte_order_mark).',
    );

    /**
     * Validate code style
     *
     * Validate line breaks and BOM
     *
     * @param string $content
     * @param string $file
     * @return bool
     */
    public function validate($content, $file)
    {
        $this->_validateTabIndents($content, $file);
        $this->_validateLineBreaks($content, $file);
        $this->_validateBom($content, $file);
        return array() == $this->_errorCollector->getErrors();
    }

    /**
     * Checks if there is any tab or windows-type line break
     *
     * @param string $content
     * @param string $file
     * @return $this
     */
    protected function _validateLineBreaks($content, $file)
    {
        //checking for windows line breaks
        if (preg_match_all('~(\r\n)~s', $content, $lnMatches)) {
            $this->_addError(
                $file,
                self::CODE_WIN_LINE_BREAK,
                count($lnMatches[0])
            );
        }
        return $this;
    }

    /**
     * Checking BOM - http://en.wikipedia.org/wiki/Byte_order_mark
     *
     * @param string $content
     * @param string $file
     * @return $this
     */
    protected function _validateBom($content, $file)
    {
        if (substr($content, 0, 3) == pack('CCC', 0xef, 0xbb, 0xbf)) {
            $this->_addError($file, self::CODE_FILE_BOM);
        }
        return $this;
    }

    /**
     * Checking TAB indents
     *
     * @param string $content
     * @param string $file
     * @return $this
     */
    protected function _validateTabIndents($content, $file)
    {
        if (preg_match('~^[^\t]*?\t~s', $content, $matches)) {
            //$line = count(explode("\n", $matches[0]));

            preg_match_all('~(\t)~s', $content, $tbMatches);
            $this->_addError(
                $file,
                self::CODE_TAB_CHAR,
                count($tbMatches[0])
            );
        }
    }
}
