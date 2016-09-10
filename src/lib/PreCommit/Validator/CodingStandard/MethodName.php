<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
namespace PreCommit\Validator\CodingStandard;

/**
 * Class CodingStandard validator
 *
 * @package PreCommit\Validator
 */
class MethodName extends UnderscoreInMethod
{
    /**#@+
     * Error codes
     */
    const CODE_PHP_CATCH                        = 'standardCatch';
    const CODE_PHP_TRY                          = 'standardTry';
    const CODE_PHP_IF_ELSE_BRACE                = 'standardElse';
    const CODE_PHP_SPACE_BRACE                  = 'spaceBrace';
    const CODE_PHP_SPACE_BRACKET                = 'spaceBracket';
    const CODE_PHP_LINE_EXCEEDS                 = 'lineLength';
    const CODE_PHP_REDUNDANT_SPACES             = 'redundantSpace';
    const CODE_PHP_CONDITION_ASSIGNMENT         = 'conditionAssignment';
    const CODE_PHP_OPERATOR_SPACES_MISSED       = 'operatorSpace';
    const CODE_PHP_PUBLIC_METHOD_NAMING_INVALID = 'publicMethodNaming';
    const CODE_PHP_METHOD_SCOPE                 = 'methodWithoutScope';
    const CODE_PHP_GAPS                         = 'redundantGaps';
    const CODE_PHP_BRACKET_GAPS                 = 'redundantGapAfterBracket';
    const CODE_PHP_LAST_FUNCTION_GAP            = 'redundantGapAfterLastFunction';
    const CODE_PHP_UNDERSCORE_IN_VAR            = 'variableHasUnderscore';
    /**#@-*/

    /**
     * Error messages
     *
     * @var array
     */
    protected $errorMessages
        = [
            self::CODE_PHP_PUBLIC_METHOD_NAMING_INVALID => 'Public method name should start with two small letters (except magic methods). Original line: %value%',
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
        $this->validateCodeStyleByLines($content, $file);

        return !$this->errorCollector->hasErrors();
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

            //check function naming and scope
            if (strpos($str, ' function ') && preg_match('#^ {4}[a-z][a-z ]+#', $str, $matches)) {
                if (!$this->isSkipMethodNameValidation($content, $str)) {
                    if (preg_match('/^\s*(static )?public /', $str)
                        && !preg_match('/public (static )?function ([a-z]{2}|__[a-z]{2})/', $str)
                    ) {
                        $this->addError($file, self::CODE_PHP_PUBLIC_METHOD_NAMING_INVALID, $currentString, $line);
                    }
                }
            }
        }

        return $this;
    }
}
