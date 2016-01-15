<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
namespace PreCommit\Validator;

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
    protected $errorMessages
        = array(
            self::CODE_TAB_CHAR       => 'Contains tab character. Total Amount of tabs: %value%.',
            self::CODE_WIN_LINE_BREAK => 'Windows line breaks found: %value%',
            self::CODE_FILE_BOM       => 'File starts with BOM (http://en.wikipedia.org/wiki/Byte_order_mark).',
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
        $this->validateTabIndents($content, $file);
        $this->validateLineBreaks($content, $file);
        $this->validateBom($content, $file);

        return !$this->errorCollector->hasErrors();
    }

    /**
     * Checking TAB indents
     *
     * @param string $content
     * @param string $file
     * @return $this
     */
    protected function validateTabIndents($content, $file)
    {
        if (preg_match('~^[^\t]*?\t~s', $content, $matches)) {
            //$line = count(explode("\n", $matches[0]));

            preg_match_all('~(\t)~s', $content, $tbMatches);
            $this->addError(
                $file,
                self::CODE_TAB_CHAR,
                count($tbMatches[0])
            );
        }
    }

    /**
     * Checks if there is any tab or windows-type line break
     *
     * @param string $content
     * @param string $file
     * @return $this
     */
    protected function validateLineBreaks($content, $file)
    {
        //checking for windows line breaks
        if (preg_match_all('~(\r\n)~s', $content, $lnMatches)) {
            $this->addError(
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
    protected function validateBom($content, $file)
    {
        if (substr($content, 0, 3) == pack('CCC', 0xef, 0xbb, 0xbf)) {
            $this->addError($file, self::CODE_FILE_BOM);
        }

        return $this;
    }
}
