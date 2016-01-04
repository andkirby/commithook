<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
namespace PreCommit\Validator;

/**
 * Class CodingStandard validator
 *
 * @package PreCommit\Validator
 */
class CodingStandardPhtml extends AbstractValidator
{
    /**#@+
     * Error codes
     */
    const CODE_PHTML_ALTERNATIVE_SYNTAX = 'nonAlterSyntax';
    const CODE_PHTML_GAPS = 'redundantGapsPhtml';
    const CODE_PHTML_UNDERSCORE_IN_VAR = 'variableHasUnderscorePhtml';
    const CODE_PHTML_PROTECTED_METHOD = 'protectedMethodUsage';
    const CODE_PHTML_CLASS = 'classUsage';
    /**#@-*/

    /**
     * Error messages
     *
     * @var array
     */
    protected $errorMessages
        = array(
            self::CODE_PHTML_ALTERNATIVE_SYNTAX => 'No ability to use braces in the PHTML code. Please use alternative syntax as if..endif. Original line: %value%',
            self::CODE_PHTML_GAPS               => 'File contains at least two gaps in succession %value% time(s).',
            self::CODE_PHTML_UNDERSCORE_IN_VAR  => 'Underscore in variable(s): %vars%. Original line: %value%',
            self::CODE_PHTML_PROTECTED_METHOD   => 'It is not possible to use protected method of $this object in a template. Original line: %value%',
            self::CODE_PHTML_CLASS              => 'It is not possible classes in templates. Original line: %value%',
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
        $this->validateGaps($content, $file);
        $this->validateCodeStyleByLines($content, $file);

        return !$this->errorCollector->hasErrors();
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
            $this->addError($file, self::CODE_PHTML_GAPS, count($match[0]));
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
        $content     = $this->filterContent($content);
        $originalArr = preg_split('/\x0A\x0D|\x0D\x0A|\x0A|\x0D/', $content);
        foreach ($originalArr as $line => $str) {
            $str = trim($str);
            if (!$str) {
                //skip empty line
                continue;
            }
            $this->validateStringAlternativeSyntaxUsage($file, $str, $line);
            $this->validateStringNoUnderscoreInVariableName($file, $str, $line);
            $this->validateStringNoProtectedMethodUsage($file, $str, $line);
            $this->validateStringNoClassesUsage($file, $str, $line);
        }

        return $this;
    }

    /**
     * Filter content
     *
     * Cut JS script code
     *
     * @param string $content
     * @return string
     */
    protected function filterContent($content)
    {
        return preg_replace('/<script(\n|\r|.)*?<\/script>/', '', $content);
    }

    /**
     * Validate string for alternative syntax usage of if..endif, foreach..endforeach, etc.
     *
     * @param string $file
     * @param string $str
     * @param int    $line
     * @return $this
     */
    protected function validateStringAlternativeSyntaxUsage($file, $str, $line)
    {
        $operators = 'elseif|else if|if|switch|foreach|for|while';
        if (preg_match('/[^A-z0-9]+(?:'.$operators.')[^A-z]?\(.*?\).*/i', $str, $b)
            && substr_count($str, '(') === substr_count($str, ')') //ignore multi-line conditions
            && !preg_match('/[^A-z0-9]+(?:'.$operators.').*?\).*?:/i', $b[0], $m)
        ) {
            $this->addError($file, self::CODE_PHTML_ALTERNATIVE_SYNTAX, $str, $line);
        }

        return $this;
    }

    /**
     * Validate string for does not exist underscore in the name of variables
     *
     * @param string $file
     * @param string $str
     * @param int    $line
     * @return $this
     */
    protected function validateStringNoUnderscoreInVariableName($file, $str, $line)
    {
        if (false !== strpos($str, '$')
            && preg_match_all('/\$\w*_\w*/', $str, $matches)
        ) {
            $vars = array();
            foreach ($matches as $value) {
                $vars[] = $value[0];
            }
            if ($vars) {
                $values = array('value' => $str, 'vars' => implode(',', $vars));
                $this->addError($file, self::CODE_PHTML_UNDERSCORE_IN_VAR, $values, $line);
            }
        }

        return $this;
    }

    /**
     * Validate string for alternative syntax usage of if..endif, foreach..endforeach, etc.
     *
     * @param string $file
     * @param string $str
     * @param int    $line
     * @return $this
     */
    protected function validateStringNoProtectedMethodUsage($file, $str, $line)
    {
        if (preg_match('/\$this-\>_[^_]/', $str)) {
            $this->addError($file, self::CODE_PHTML_PROTECTED_METHOD, $str, $line);
        }

        return $this;
    }

    /**
     * Validate string into disuse of classes
     *
     * @param string $file
     * @param string $str
     * @param int    $line
     * @return $this
     */
    protected function validateStringNoClassesUsage($file, $str, $line)
    {
        if (preg_match('/[A-z_]{3,}\:\:[A-z_]/', $str)) {
            $this->addError($file, self::CODE_PHTML_CLASS, $str, $line);
        }

        return $this;
    }
}
