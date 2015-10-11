<?php
namespace PreCommit\Validator;

/**
 * Class of validator for calling parent method described in PHPDoc
 *
 * @package PreCommit\Validator
 */
class ParentThis extends AbstractValidator
{
    /**#@+
     * Error codes
     */
    const CODE_PHP_RETURN_NOT_THIS = 'phpReturnNotUsesThis';
    /**#@-*/

    /**
     * Error messages
     *
     * @var array
     */
    protected $_errorMessages
        = array(
            self::CODE_PHP_RETURN_NOT_THIS => '@return PHPDoc tag uses parent class %value%. Probably it should be replaced with "@return $this".',
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
        $parentClass = $this->_getExtendClass($content);
        if (!$parentClass) {
            return false;
        }
        $this->_validateParentClassInReturn($parentClass, $content, $file);

        $parentClassAlias = $this->_getClassAlias($parentClass, $content);
        if ($parentClassAlias) {
            $this->_validateParentClassInReturn($parentClassAlias, $content, $file);
        }
        return !$this->_errorCollector->hasErrors();
    }

    /**
     * Get parent class name
     *
     * @param string $content
     * @return string|null
     */
    protected function _getExtendClass($content)
    {
        $matches = array();
        preg_match('/ extends[ ]+([A-z0-9_\x92]+)/', $content, $matches);
        return isset($matches[1]) ? $matches[1] : null;
    }

    /**
     * Get parent class name
     *
     * @param string $class
     * @param string $content
     * @return null|string
     */
    protected function _getClassAlias($class, $content)
    {
        $matches = array();
        if (strpos($class, '\\')) {
            if (0 === strpos($class, '\\')) {
                //ignore absolute class path
                return null;
            }
            $parentPath = substr($class, 0, strpos($class, "\x5C"));
            if (
                preg_match('~use ([A-z0-9\x5C_]+)\x5C' . $parentPath . ';~', $content, $matches)
                || preg_match('~use ([A-z0-9\x5C_]+) as ' . $parentPath . ';~', $content, $matches)
            ) {
                return $matches[1] . substr($class, strpos($class, "\x5C"));
            }
        }
        preg_match("~use $class as ([A-z0-9_]+);~", $content, $matches);
        if (!empty($matches[1])) {
            return $matches[1];
        }
        preg_match('~use ([A-z0-9\\_]+\\' . $class . ');~', $content, $matches);
        if (!empty($matches[1])) {
            return $matches[1];
        }
        preg_match('~use ([A-z0-9\\_]+) as ' . $class . ';~', $content, $matches);
        if (!empty($matches[1])) {
            return $matches[1];
        }
        return null;
    }

    /**
     * Check if parent class matches with
     *
     * @param string $parentClass
     * @param string $content
     * @param string $file
     * @return $this
     */
    protected function _validateParentClassInReturn($parentClass, $content, $file)
    {
        $regularClass = ltrim($parentClass, '\\'); //remove left "\"
        $regularClass = str_replace('\\', '\x5C', $regularClass); //set codes instead "\"
        if (preg_match('~ +\* @return +\x5C?' . $regularClass . '~', $content)) {
            $this->_addError($file, self::CODE_PHP_RETURN_NOT_THIS, $parentClass, null);
        }
        return $this;
    }
}
