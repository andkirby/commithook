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
    protected $_errorMessages = array(
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

        return !$this->_errorCollector->hasErrors();
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
        if (preg_match('/@return[ ]+\\\?' . ltrim($parentClass, '\\') . '/', $content)) {
            $this->_addError($file, self::CODE_PHP_RETURN_NOT_THIS, $parentClass, null);
        }
        return $this;
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
}
