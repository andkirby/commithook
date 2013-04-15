<?php
namespace PreCommit\Validator;

/**
 * Class CodingStandard validator
 *
 * @package PreCommit\Validator
 */
class CodingStandard extends AbstractValidator
{
    /**#@+
     * Error codes
     */
    const CODE_PHP_CATCH                    = 'standardCatch';
    const CODE_PHP_TRY                      = 'standardTry';
    const CODE_PHP_IF_ELSE_BRACE            = 'standardElse';
    const CODE_PHP_SPACE_BRACE              = 'spaceBrace';
    const CODE_PHP_SPACE_BRACKET            = 'spaceBracket';
    const CODE_PHP_LINE_EXCEEDS             = 'lineLength';
    const CODE_PHP_REDUNDANT_SPACES         = 'redundantSpace';
    const CODE_PHP_CONDITION_ASSIGNMENT     = 'conditionAssignment';
    const CODE_PHP_OPERATOR_SPACES_MISSED   = 'operatorSpace';
    /**#@-*/

    /**
     * Error messages
     *
     * @var array
     */
    protected $_errorMessages = array(
        self::CODE_PHP_TRY => "Syntax in TRY instruction is wrong. Original line: %value%",
        self::CODE_PHP_CATCH => "Syntax in CATCH instruction is wrong. Original line: %value%",
        self::CODE_PHP_IF_ELSE_BRACE => 'Syntax of {} in IF..ELSE instruction is wrong. Original line: %value%',
        self::CODE_PHP_SPACE_BRACE => 'Spaces missed near {. Original line: %value%',
        self::CODE_PHP_SPACE_BRACKET => 'Spaces missed near (. Original line: %value%',
        self::CODE_PHP_LINE_EXCEEDS => 'Length exceeds 120 chars.',
        self::CODE_PHP_REDUNDANT_SPACES => 'Additional spaces found. Original line: %value%',
        self::CODE_PHP_CONDITION_ASSIGNMENT => 'Assignment in condition is not allowed. Avoid usage of next structures: "if (\$a = time()) {" Original line: %value%',
        self::CODE_PHP_OPERATOR_SPACES_MISSED => 'Spaces are required before and after operators(<>=.-+&%*). Original line: %value%',
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
            if (
                preg_match('/\S=\>|=\>\S/i', $str) // operator => must be wrapped with spaces
                || preg_match('/[^\s(]\!/i', $str) // operators != !== must have preceding space
                || preg_match('/(?:=[^=\s<>])|(?:[^-=\s!+*\/\.%&|^<>]=)/i', $str, $mm) // operators = == === must be wrapped with spaces
                || preg_match('/[-+*\/%,][^-+*\/%=<>;$\)\s\]][^\]]|[^\S][^-+*\/%=<>;$\)\s][-+*\/%]/i', $str) // math operators (+-*/% and comma(,)) must be wrapped with spaces
                //|| preg_match('/\S[^-=<>][<>]{1,2}[^\s<>;\)]/i', $str)                   // operators > < >> << must be wrapped with spaces
                || preg_match('/[^\(\s&]&{1,2}|&{1,2}[^\s&]/i', $str, $mm) // operator & && must be wrapped with spaces
            ) {
                $this->_addError($file, self::CODE_PHP_OPERATOR_SPACES_MISSED, $currentString, $line);
            }

            //checking for an assignment in a condition
            if (preg_match('~if\s*\((.*?)\)\s*[{:]~s', $str, $matches)) {
                if (preg_match('~\$[^= ]+ ?= ?[^=]~', $matches[1])) {
                    $this->_addError($file, self::CODE_PHP_CONDITION_ASSIGNMENT, $currentString, $line);
                }
            }

            if (preg_match('/(?:[,\(\)\{\}=]\s{2,}|\w\s{2,}[\(\)\{\}]|\s+[,]|\S\s+[)]|[(]\s+)/i', $str)) {
                $this->_addError($file, self::CODE_PHP_REDUNDANT_SPACES, $currentString, $line);
            }

            if (strlen($str) > 120) {
                $this->_addError($file, self::CODE_PHP_LINE_EXCEEDS, null, $line);
            }

            $reg = '/\s*[^A-z0-9]((?:elseif|else if|if|switch|foreach|for|while|do))(\W+[^\(]*)[^\)]*([^\x0A\x0D]*)/i';
            if (preg_match($reg, $str, $match)) {
                if (0 === strpos(trim($match[1]), 'do') && trim($str) !== 'do {') {
                    $this->_addError($file, self::CODE_PHP_SPACE_BRACKET, $currentString, $line);
                } elseif (substr(trim($match[0]), -3) != ') {') {
                    $this->_addError($file, self::CODE_PHP_SPACE_BRACKET, $currentString, $line);
                }
            }
            if (false !== stripos($str, 'else') && preg_match('/(\W+else(?: ?if).*)/i', $str, $match)) {
                $hasIf = false !== strpos($match[1], 'else if') || false !== strpos($match[1], 'elseif');
                if (!empty($match[1])
                    && (!$hasIf && strpos($match[1], '{') === false
                        || strpos($match[1], '}') === false)
                ) {
                    $this->_addError($file, self::CODE_PHP_IF_ELSE_BRACE, $currentString, $line);
                }
                if (preg_match('/(else\{|\}else)/i', $match[1])) {
                    $this->_addError($file, self::CODE_PHP_IF_ELSE_BRACE, $currentString, $line);
                }
            }

            if (preg_match('/[^A-z]try/i', $str) && !preg_match('/^(\s+try \{)$/i', $str, $match)) {
                $this->_addError($file, self::CODE_PHP_TRY, $currentString, $line);
            } elseif (preg_match('/[^A-z]catch/i', $str)
                && !preg_match('/^\s+(\} catch \([A-z0-9_\\]+ \$[A-z0-9_]+\) \{)$/', $str, $m)
            ) {
                $this->_addError($file, self::CODE_PHP_CATCH, $currentString, $line);
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
