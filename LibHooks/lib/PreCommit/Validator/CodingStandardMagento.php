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
        $parsedArr = $this->_splitContent($content);
        foreach ($parsedArr as $line => $str) {
            $currentString = trim($originalArr[$line - 1]);
            //check using Mage::throwException();
            if (false !== strpos($str, 'Mage::throwException(')) {
                $this->_addError($file, self::CODE_PHP_DEPRECATED_THROW_EXCEPTION, $currentString, $line);
            }
        }

        return array() == $this->_errorCollector->getErrors();
    }

    /**
     * Cut from content text in quotes and comments
     *
     * @param string $content
     * @todo Refactor method
     * @return array
     */
    protected function _splitContent($content)
    {
        $parsedArr = array();
        $length = strlen($content);

        $cleanedText = '';
        $state = 0;
        $line = 1;
        for ($i = 0; $i < $length; $i++) {
            $byte = $content[$i];
            switch ($state) {
                case 1: //in single quotes
                    if ($byte == '\'' && $i > 1 && $content[$i - 1] != '\\') {
                        $state = 0;
                    }
                    $cleanedText .= $byte;
                    if ("\x0A" == $byte || "\x0D" == $byte &&
                        ($i < $length - 1 && $content[$i + 1] != "\x0A"
                            && $content[$i + 1] != "\x0D")
                    ) {
                        $line++;
                    }
                    break;

                case 2: //in double quotes
                    if ($byte == '"' && $i > 1 && $content[$i - 1] != '\\') {
                        $state = 0;
                    }
                    $cleanedText .= $byte;
                    if ("\x0A" == $byte || "\x0D" == $byte
                        && ($i < $length - 1 && $content[$i + 1] != "\x0D" && $content[$i + 1] != "\x0D")
                    ) {
                        $line++;
                    }
                    break;

                case 3: //in // comments
                    if (preg_match('/[\x00-\x0D]/', $byte)) {
                        $state = 0;
                    }
                    if ("\x0A" == $byte || "\x0D" == $byte
                        && ($i < $length - 1 && $content[$i + 1] != "\x0A" && $content[$i + 1] != "\x0D")
                    ) {
                        $line++;
                    }
                    break;

                case 4: //in /**/ comments
                    if ($byte == '*' && $i < $length - 1 && $content[$i + 1] == '/') {
                        $state = 0;
                        $i++;
                    }
                    if ("\x0A" == $byte || "\x0D" == $byte
                        && ($i < $length - 1 && $content[$i + 1] != "\x0A" && $content[$i + 1] != "\x0D")
                    ) {
                        $line++;
                    }
                    break;

                default:
                    if ("\x0A" == $byte || "\x0D" == $byte &&
                        ($i < $length - 1 && $content[$i + 1] != "\x0A" && $content[$i + 1] != "\x0D")
                    ) {
                        $line++;
                    }

                    if ($byte == '\'' && $i > 1 && $content[$i - 1] != '\\') {
                        $state = 1;
                    } elseif ($byte == '"' && $i > 1 && $content[$i - 1] != '\\') {
                        $state = 2;
                    } elseif ($byte == '/' && $i < $length - 1 && $content[$i + 1] == '/') {
                        $i++;
                        $state = 3;
                        continue;
                    } elseif ($byte == '/' && $i < $length - 1 && $content[$i + 1] == '*') {
                        $i++;
                        $state = 4;
                        continue;
                    }

                    //outside comments and quotes
                    $cleanedText .= $byte;
                    @$parsedArr[$line] .= ("\x0A" == $byte || "\x0D" == $byte) ? '' : $byte;
            }
        }
        return $parsedArr;
    }
}
