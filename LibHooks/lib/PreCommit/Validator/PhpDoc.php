<?php
namespace PreCommit\Validator;

/**
 * Class XML validator
 *
 * @package PreCommit\Validator
 */
class PhpDoc extends AbstractValidator
{
    /**#@+
     * Error codes
     */
    const CODE_PHP_DOC_MISSED            = 'phpDocMissed';
    const CODE_PHP_DOC_MESSAGE           = 'phpDocMessageMissed';
    const CODE_PHP_DOC_ENTER_DESCRIPTION = 'phpDocEnterDescription';
    const CODE_PHP_DOC_UNKNOWN           = 'phpDocUnknown';
    /**#@-*/

    /**
     * Error messages
     *
     * @var array
     */
    protected $_errorMessages = array(
        self::CODE_PHP_DOC_ENTER_DESCRIPTION => 'PHPDoc is incomplete info: Enter description here... - Please, write a reasonable description.',
        self::CODE_PHP_DOC_UNKNOWN           => "PHPDoc is incomplete info: 'unknown_type' - Please, specify a type.",
        self::CODE_PHP_DOC_MISSED            => 'PHPDoc is missing for %value%',
        self::CODE_PHP_DOC_MESSAGE           => 'There is PHPDoc message missed for %value%',
    );

    /**
     * Validate PhpDocs
     *
     * @param string $content
     * @param string $file
     * @return bool
     */
    public function validate($content, $file)
    {
        //clean up group comments with nodes
        $content = $this->_cleanGroupCommentedNodes($content);

        $text = preg_split('/\x0A\x0D|\x0D\x0A|\x0A|\x0D/', $content);

        foreach ($text as $line => $str) {
            $line++;
            $this->_validateEnterDescription($file, $str, $line);
            $this->_validateUnknownType($file, $str, $line);
        }
        $this->_validateExistPhpDocsForClassItems($content, $file);
        $this->_validateExistPhpDocForClass($content, $file);
        $this->_validateExistPhpDocMessage($content, $file);

        return array() == $this->_errorCollector->getErrors();
    }

    /**
     * Validate PHPDoc for contained "Enter description here..."
     *
     * @param string $file
     * @param string $str
     * @param string $line
     * @return $this
     */
    protected function _validateEnterDescription($file, $str, $line)
    {
        if (preg_match('/\*\s*Enter description here/i', $str)) {
            $this->_addError($file, self::CODE_PHP_DOC_ENTER_DESCRIPTION, null, $line);
        }
        return $this;
    }

    /**
     * Validate PHPDoc for contained unknown_type
     *
     * @param string $file
     * @param string $str
     * @param string $line
     * @return $this
     */
    protected function _validateUnknownType($file, $str, $line)
    {
        if (preg_match('/\*\s*unknown_type/i', $str)) {
            $this->_addError($file, self::CODE_PHP_DOC_UNKNOWN, null, $line);
        }
        return $this;
    }

    /**
     * Validate exist PHPDoc for class items
     *
     * @param string $content
     * @param string $file
     * @return $this
     */
    protected function _validateExistPhpDocsForClassItems($content, $file)
    {
        $reg = '/(?<!\*\/\x0D|\*\/)\x0A\x20{4}((?:public function|protected function|private function'
            . '|function|const|public|protected|private)[^\x0A]*)/i';
        if (preg_match_all($reg, $content, $matches)) {
            foreach ($matches[1] as $match) {
                $this->_addError($file, self::CODE_PHP_DOC_MISSED, $match);
            }
        }
        return $this;
    }

    /**
     * Validate exist PHPDoc for class
     *
     * @param string $content
     * @param string $file
     * @return $this
     */
    public function _validateExistPhpDocForClass($content, $file)
    {
        if (preg_match_all('/(?<!\*\/\x0D|\*\/)\x0A(class[^\x0A]*)/i', $content, $matches)) {
            foreach ($matches[1] as $match) {
                $this->_addError($file, self::CODE_PHP_DOC_MISSED, $match);
            }
        }
        return $this;
    }

    /**
     * Validate exist PHPDoc Message
     *
     * @param string $content
     * @param string $file
     * @return $this
     */
    public function _validateExistPhpDocMessage($content, $file)
    {
        if (preg_match_all(
            '/\x20{4}\/\*\*\x0D?\x0A\x20{5}\*\s\@(.|\x0D?\x0A)*?\*\/\x0D?\x0A\x20(.*)/', $content, $matches
        )) {
            foreach ($matches[2] as $match) {
                $this->_addError($file, self::CODE_PHP_DOC_MESSAGE, $match);
            }
        }
        return $this;
    }

    /**
     * Remove Group commented nodes
     *
     * @param string $content
     * @return string
     */
    protected function _cleanGroupCommentedNodes($content)
    {
        return preg_replace('/\s*\/\*\*\#\@\+(\s|\S)*?\/\*\*\#@\-\*\//', '', $content);
    }
}
