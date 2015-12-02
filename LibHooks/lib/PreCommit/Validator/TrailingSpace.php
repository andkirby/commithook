<?php
namespace PreCommit\Validator;

/**
 * Class Trailing spaces validator
 *
 * @package PreCommit\Validator
 */
class TrailingSpace extends AbstractValidator
{
    /**#@+
     * Error codes
     */
    const CODE_PHP_REDUNDANT_TRAILING_SPACES = 'redundantTrailingSpaces';

    const CODE_PHP_NO_END_TRAILING_LINE      = 'noTrailingEndLine';

    /**#@-*/

    /**
     * Error messages
     *
     * @var array
     */
    protected $errorMessages
        = array(
            self::CODE_PHP_REDUNDANT_TRAILING_SPACES => 'Contains trailing space(s) at lease %value% times.',
            self::CODE_PHP_NO_END_TRAILING_LINE      => 'Missing trailing line in the end of file.',
        );

    /**
     * Checking for interpreter errors
     *
     * @param string $content
     * @param string $file
     * @return bool
     */
    public function validate($content, $file)
    {
        $this->_validateRedundantTrailingSpaces($content, $file);
        $this->_validateTrailingLine($content, $file);

        return !$this->errorCollector->hasErrors();
    }

    /**
     * Check redundant trailing spaces
     *
     * @param string $content
     * @param string $file
     * @return $this
     */
    protected function _validateRedundantTrailingSpaces($content, $file)
    {
        $matches = array();
        if (preg_match_all("~.*?[ \t]+\r?\n~", $content, $matches)) {
            $this->_addError(
                $file,
                self::CODE_PHP_REDUNDANT_TRAILING_SPACES,
                count($matches[0])
            );
        }

        return $this;
    }

    /**
     * Check mandatory trailing line in the end of file
     *
     * @param string $content
     * @param string $file
     * @return $this
     */
    protected function _validateTrailingLine($content, $file)
    {
        $lines    = explode("\n", $content);
        $lastLine = array_pop($lines);
        if ($lastLine != '') {
            $this->_addError($file, self::CODE_PHP_NO_END_TRAILING_LINE);
        }

        return $this;
    }
}
