<?php
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
    const CODE_PHTML_GAPS               = 'redundantGapsPhtml';
    const CODE_PHTML_UNDERSCORE_IN_VAR  = 'variableHasUnderscorePhtml';
    const CODE_PHTML_PROTECTED_METHOD   = 'protectedMethodUsage';
    /**#@-*/

    /**
     * Error messages
     *
     * @var array
     */
    protected $_errorMessages = array(
        self::CODE_PHTML_ALTERNATIVE_SYNTAX => 'No ability to use braces in the PHTML code. Please use alternative syntax as if..endif. Original line: %value%',
        self::CODE_PHTML_GAPS               => 'File contain at least two gaps in succession %value% time(s).',
        self::CODE_PHTML_UNDERSCORE_IN_VAR  => 'Underscore in variable(s): %vars%. Original line: %value%',
        self::CODE_PHTML_PROTECTED_METHOD   => 'No ability to use protected method of $this object in a template. Original line: %value%',
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
        $this->_validateGaps($content, $file);
        $this->_validateCodeStyleByLines($content, $file);

        return array() == $this->_errorCollector->getErrors();
    }

    /**
     * Validate content gaps
     *
     * @param string $content
     * @param string $file
     * @return $this
     */
    public function _validateGaps($content, $file)
    {
        $content = preg_replace('/\r/', '', $content);

        preg_match_all('/\n\n\n/', $content, $match);
        if ($match[0]) {
            $this->_addError($file, self::CODE_PHTML_GAPS, count($match[0]));
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
    protected function _validateCodeStyleByLines($content, $file)
    {
        $content = $this->_filterContent($content);
        $originalArr = preg_split('/\x0A\x0D|\x0D\x0A|\x0A|\x0D/', $content);
        foreach ($originalArr as $line => $str) {
            $str = trim($str);
            if (!$str) {
                //skip empty line
                continue;
            }
            $this->_validateStringAlternativeSyntaxUsage($file, $str, $line);
            $this->_validateStringNoUnderscoreInVariableName($file, $str, $line);
            $this->_validateStringNoProtectedMethodUsage($file, $str, $line);
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
    protected function _validateStringNoUnderscoreInVariableName($file, $str, $line)
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
                $this->_addError($file, self::CODE_PHTML_UNDERSCORE_IN_VAR, $values, $line);
            }
        }
        return $this;
    }

    /**
     * Validate string for alternative syntax usage of if..endif, foreach..endforeach, etc.
     *
     * @param string $file
     * @param string $str
     * @param int $line
     * @return $this
     */
    protected function _validateStringAlternativeSyntaxUsage($file, $str, $line)
    {
        $operators = 'elseif|else if|if|switch|foreach|for|while';
        if (preg_match('/[^A-z0-9]+(?:' . $operators . ')[^A-z]?\(.*?\).*/i', $str, $b)
            && !preg_match('/[^A-z0-9]+(?:' . $operators . ').*?\).*?:/i', $b[0], $m)
        ) {
            $this->_addError($file, self::CODE_PHTML_ALTERNATIVE_SYNTAX, $str, $line);
        }
        return $this;
    }

    /**
     * Validate string for alternative syntax usage of if..endif, foreach..endforeach, etc.
     *
     * @param string $file
     * @param string $str
     * @param int $line
     * @return $this
     */
    protected function _validateStringNoProtectedMethodUsage($file, $str, $line)
    {
        if (preg_match('/\$this-\>_[^_]/', $str)) {
            $this->_addError($file, self::CODE_PHTML_PROTECTED_METHOD, $str, $line);
        }
        return $this;
    }

    /**
     * Filter content
     *
     * Cut JS script
     *
     * @param string $content
     * @return string
     */
    protected function _filterContent($content)
    {
        return preg_replace('/<script(\n|\r|.)*?<\/script>/', '', $content);
    }
}
