<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
namespace PreCommit\Validator;

use PreCommit\Validator\Helper\LineFinder;

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
    const CODE_PHP_CATCH                                         = 'standardCatch';
    const CODE_PHP_TRY                                           = 'standardTry';
    const CODE_PHP_IF_ELSE_BRACE                                 = 'standardElse';
    const CODE_PHP_SPACE_BRACE                                   = 'spaceBrace';
    const CODE_PHP_SPACE_BRACKET                                 = 'spaceBracket';
    const CODE_PHP_LINE_EXCEEDS                                  = 'lineLength';
    const CODE_PHP_REDUNDANT_SPACES                              = 'redundantSpace';
    const CODE_PHP_CONDITION_ASSIGNMENT                          = 'conditionAssignment';
    const CODE_PHP_OPERATOR_SPACES_MISSED                        = 'operatorSpace';
    const CODE_PHP_PUBLIC_METHOD_NAMING_INVALID                  = 'publicMethodNaming';
    const CODE_PHP_METHOD_SCOPE                                  = 'methodWithoutScope';
    const CODE_PHP_GAPS                                          = 'redundantGaps';
    const CODE_PHP_BRACKET_GAPS                                  = 'redundantGapAfterBracket';
    const CODE_PHP_LAST_FUNCTION_GAP                             = 'redundantGapAfterLastFunction';
    const CODE_PHP_UNDERSCORE_IN_VAR                             = 'variableHasUnderscore';
    /**#@-*/

    /**
     * Error messages
     *
     * @var array
     */
    protected $errorMessages
        = [
            self::CODE_PHP_TRY                                           => "Syntax in TRY instruction is wrong. Original line: %value%",
            self::CODE_PHP_CATCH                                         => "Syntax in CATCH instruction is wrong. Original line: %value%",
            self::CODE_PHP_IF_ELSE_BRACE                                 => 'Syntax of {} in IF..ELSE instruction is wrong. Original line: %value%',
            self::CODE_PHP_SPACE_BRACE                                   => 'Spaces missed near {. Original line: %value%',
            self::CODE_PHP_SPACE_BRACKET                                 => 'Spaces missed near (. Original line: %value%',
            self::CODE_PHP_LINE_EXCEEDS                                  => 'Length exceeds 120 chars.',
            self::CODE_PHP_REDUNDANT_SPACES                              => 'Additional spaces found. Original line: %value%',
            self::CODE_PHP_CONDITION_ASSIGNMENT                          => 'Assignment in condition is not allowed. Avoid usage of next structures: "if (\$a = time()) {" Original line: %value%',
            self::CODE_PHP_OPERATOR_SPACES_MISSED                        => 'Spaces are required before and after operators(<>=.-+&%*). Original line: %value%',
            self::CODE_PHP_PUBLIC_METHOD_NAMING_INVALID                  => 'Public method name should start with two small letters (except magic methods). Original line: %value%',
            self::CODE_PHP_METHOD_SCOPE                                  => 'Method should have scope: public or protected. Original line: %value%',
            self::CODE_PHP_GAPS                                          => 'File contains at least two gaps in succession %value% time(s).',
            self::CODE_PHP_BRACKET_GAPS                                  => 'File contains at least one gap after opened bracket/brace or before closed bracket/brace %value% time(s).',
            self::CODE_PHP_UNDERSCORE_IN_VAR                             => 'Underscore in variable(s): %vars%. Original line: %value%',
        ];

    /**
     * Validate content
     *
     * @param string $content
     * @param string $file
     * @return bool
     */
    public function validate($content, $file)
    {
        $this->validateGaps($content, $file);
        $this->validateCodeStyleByLines($content, $file);

        return !$this->errorCollector->hasErrors();
    }

    /**
     * Cut from content text in quotes and comments
     *
     * @param string $content
     * @todo Refactor method
     * @return array
     */
    public static function splitContent($content)
    {
        $parsedArr = [];
        $length    = strlen($content);

        $cleanedText = '';
        $state       = 0;
        $line        = 1;
        for ($i = 0; $i < $length; $i++) {
            $byte = $content[$i];
            switch ($state) {
                case 1: //in single quotes
                    if ($byte == '\'' && $i > 1
                        && ($content[$i - 1] != '\\'
                            || $content[$i - 1] == '\\' && $content[$i - 2] == '\\')
                    ) {
                        $state = 0;
                    }
                    $cleanedText .= $byte;
                    if ("\x0A" == $byte
                        || "\x0D" == $byte
                           && ($i < $length - 1 && $content[$i + 1] != "\x0A"
                               && $content[$i + 1] != "\x0D")
                    ) {
                        $line++;
                    }
                    if ($state === 0) {
                        @$parsedArr[$line] .= ("\x0A" == $byte || "\x0D" == $byte) ? '' : $byte;
                    }
                    break;

                case 2: //in double quotes
                    if ($byte == '"' && $i > 1 && $content[$i - 1] != '\\') {
                        $state = 0;
                    }
                    $cleanedText .= $byte;
                    if ("\x0A" == $byte
                        || "\x0D" == $byte
                           && ($i < $length - 1 && $content[$i + 1] != "\x0D" && $content[$i + 1] != "\x0D")
                    ) {
                        $line++;
                    }
                    break;

                case 3: //in // comments
                    if (preg_match('/[\x00-\x0D]/', $byte)) {
                        $state = 0;
                    }
                    if ("\x0A" == $byte
                        || "\x0D" == $byte
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
                    if ("\x0A" == $byte
                        || "\x0D" == $byte
                           && ($i < $length - 1 && $content[$i + 1] != "\x0A" && $content[$i + 1] != "\x0D")
                    ) {
                        $line++;
                    }
                    break;

                default:
                    if ("\x0A" == $byte
                        || "\x0D" == $byte
                           && ($i < $length - 1 && $content[$i + 1] != "\x0A" && $content[$i + 1] != "\x0D")
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

    /**
     * Validate content gaps
     *
     * @param string $content
     * @param string $file
     * @return $this
     */
    protected function validateGaps($content, $file)
    {
        $content = preg_replace('/\r/', '', $content);

        preg_match_all('/\n\n\n/', $content, $match);
        if ($match[0]) {
            $this->addError($file, self::CODE_PHP_GAPS, count($match[0]));
        }

        preg_match_all('/(\{|\()\n\n.*|.*\n\n[ ]*(\}|\))/', $content, $match);
        if ($match[0]) {
            //region Find lines
            $findings = $match[0];
            sort($findings);
            $findings = array_unique($findings);
            $lines    = [];
            foreach ($findings as $find) {
                $lines = array_merge($lines, $this->findLines($find, $content));
            }
            sort($lines);
            //endregion

            $this->addError($file, self::CODE_PHP_BRACKET_GAPS, count($match[0]), $lines);
        }

        return $this;
    }

    /**
     * Validate code style by lines
     *
     * @param string $content
     * @param string $file
     * @return $this
     */
    protected function validateCodeStyleByLines($content, $file)
    {
        $originalArr = preg_split('/\x0A\x0D|\x0D\x0A|\x0A|\x0D/', $content);
        $parsedArr   = $this->splitContent($content);
        $regPlusMin  = '/([-+*\x2F%,][^-+*\x2F%=<>;$\)\s \x5D\'][^\x5D\)0-9])|([^\S][^-+*\x2F%=<>;$\)\s ][-+*\x2F%])/i';
        foreach ($parsedArr as $line => $str) {
            if (!$str) {
                //skip empty line
                continue;
            }

            if (!isset($originalArr[$line - 1])) {
                //skip not exit line
                //TODO investigate this case.
                continue;
            }

            $currentString = trim($originalArr[$line - 1]);
            if (preg_match('/\S=\>|=\>\S/i', $str) // operator => must be wrapped with spaces
                || preg_match('/[^\s(]\!/i', $str) // operators != !== must have preceding space

                // operators = == === must be wrapped with spaces
                || preg_match('/(?:=[^=\s<>])|(?:[^-=\s!+*\x2F\.%&|^<>]=)/i', $str)

                // math operators (+-*/% and comma(,)) must be wrapped with spaces
                || preg_match($regPlusMin, $str)

                // operators > < >> << must be wrapped with spaces
                //|| preg_match('/\S[^-=<>][<>]{1,2}[^\s<>;\)]/i', $str)

                // operator & && must be wrapped with spaces
                || preg_match('/[^\(\s&]&{1,2}|&{1,2}[^\s&$]/i', $str)
            ) {
                $this->addError($file, self::CODE_PHP_OPERATOR_SPACES_MISSED, $currentString, $line);
            }

            //checking for an assignment in a condition
            if (preg_match('~if\s*\((.*?)\)\s*[{:]~s', $str, $a)) {
                if (preg_match('~\$[^= ]+ ?= ?[^=]~', $a[1])) {
                    $this->addError($file, self::CODE_PHP_CONDITION_ASSIGNMENT, $currentString, $line);
                }
            }

            if (preg_match('/(?:[,\(\)\{\}=]\s{2,}|\w\s{2,}[\(\)\{\}]|\s+[,]|\S\s+[)]|[(]\s+)/i', $str)) {
                $this->addError($file, self::CODE_PHP_REDUNDANT_SPACES, $currentString, $line);
            }

            if (strlen($str) > 120) {
                $this->addError($file, self::CODE_PHP_LINE_EXCEEDS, null, $line);
            }

            $operators = 'elseif|else if|else|if|switch|foreach|for|while|do';
            $reg       = '/\s*[^A-z0-9$_]+((?:'.$operators.'))(\W*[^\(]*)[^\)]*([^\x0A\x0D]*)/i';

            if (preg_match($reg, $str, $b)
                && !preg_match('/^[A-z0-9_]/', $b[2])
            ) {
                if ($b[1] == 'do' || $b[1] == 'try') {
                    if (preg_match('/^[^A-z0-9\>\$]*(try|do)[^A-z0-9-\$]*$/', trim($str))
                        && trim($str) !== $b[1].' {'
                    ) {
                        $this->addError($file, self::CODE_PHP_SPACE_BRACKET, $currentString, $line);
                    }
                } elseif ($b[1] == 'while') {
                    if (substr(trim($str), -1) == ';' && !preg_match('/^\s+\} while \(.*\);$/', $str)
                        || substr(trim($str), -1) != ';' && !preg_match('/^\s+while \(.*\) {$/', $str)
                    ) {
                        $this->addError($file, self::CODE_PHP_SPACE_BRACKET, $currentString, $line);
                    }
                } elseif ($b[1] == 'else') {
                    if (!preg_match('/\s*} else {$/i', $str)) {
                        $this->addError($file, self::CODE_PHP_SPACE_BRACKET, $currentString, $line);
                    }
                } elseif (substr(trim($b[0]), -3) != ') {') {
                    $bracketRight = substr_count($b[0], ')');
                    $bracketLeft  = substr_count($b[0], '(');
                    if ($bracketLeft >= 1 && $bracketLeft == $bracketRight) {
                        $this->addError($file, self::CODE_PHP_SPACE_BRACKET, $currentString, $line);
                    }
                } elseif (substr(ltrim($b[0], ' }'), strlen($b[1]), 1) != ' ') { //check right space after construct
                    $this->addError($file, self::CODE_PHP_SPACE_BRACKET, $currentString, $line);
                }
            }

            //check try..catch
            if (preg_match('/[^A-z]try[^A-z]/i', $str) && !preg_match('/^(\s+try \{)$/i', $str, $b)) {
                $this->addError($file, self::CODE_PHP_SPACE_BRACKET, $currentString, $line);
            } elseif (preg_match('/[^A-z]catch/i', $str)
                      && !preg_match('/^\s*(\} catch \([A-z0-9_\\]+ \$[A-z0-9_]+\) \{)$/', $str, $m)
            ) {
                $this->addError($file, self::CODE_PHP_SPACE_BRACKET, $currentString, $line);
            }

            //check function naming and scope
            if (strpos($str, ' function ') && preg_match('#^ {4}[a-z][a-z ]+#', $str, $matches)) {
                if (!preg_match('/(protected|private|public) (static )?function/', $str)) {
                    $this->addError($file, self::CODE_PHP_METHOD_SCOPE, $currentString, $line);
                }
            }

            //check underscore in variable name
            if (false !== strpos($str, '$')
                && false === strpos($str, ' public ')
                && false === strpos($str, ' protected ')
                && false === strpos($str, ' private ')
                && false === strpos($str, ' static ')
                && preg_match_all('/[:]?\$\w*_\w*/', $str, $matches)
            ) {
                $vars = [];
                foreach ($matches as $value) {
                    if (0 !== strpos($value[0], ':')) {
                        $vars[] = $value[0];
                    }
                }
                if ($vars) {
                    $values = ['value' => $currentString, 'vars' => implode(',', $vars)];
                    $this->addError($file, self::CODE_PHP_UNDERSCORE_IN_VAR, $values, $line);
                }
            }
        }

        return $this;
    }

    /**
     * Find lines for a string
     *
     * @param string $find
     * @param string $content
     * @param bool   $once
     * @return array|int
     */
    protected function findLines($find, $content, $once = false)
    {
        return LineFinder::findLines($find, $content, $once);
    }
}
