<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
namespace PreCommit\Validator;

use PreCommit\Validator\Helper\LineFinder;

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
    protected $errorMessages
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
        $parentClass = $this->getExtendClass($content);
        if (!$parentClass) {
            return false;
        }
        $this->validateParentClassInReturn($parentClass, $content, $file);

        $parentClassAlias = $this->getClassAlias($parentClass, $content);
        if ($parentClassAlias) {
            $this->validateParentClassInReturn($parentClassAlias, $content, $file);
        }

        return !$this->errorCollector->hasErrors();
    }

    /**
     * Get parent class name
     *
     * @param string $content
     * @return string|null
     */
    protected function getExtendClass($content)
    {
        $matches = array();
        preg_match('/ extends[ ]+([A-z0-9_\x92]+)/', $content, $matches);

        return isset($matches[1]) ? $matches[1] : null;
    }

    /**
     * Check if parent class matches with
     *
     * @param string $parentClass
     * @param string $content
     * @param string $file
     * @return $this
     */
    protected function validateParentClassInReturn($parentClass, $content, $file)
    {
        $regularClass = ltrim($parentClass, '\\'); //remove left "\"
        $regularClass = str_replace('\\', '\x5C', $regularClass); //set codes instead "\"
        if (preg_match_all('~[ ]+\* @return +\x5C?'.$regularClass.'\x0A~', $content, $match)) {
            if ($match[0]) {
                //region Find lines
                $findings = $match[0];
                sort($findings);
                $findings = array_unique($findings);
                $lines    = array();
                foreach ($findings as $find) {
                    $lines = array_merge($lines, $this->findLines($find, $content));
                }
                sort($lines);
                //endregion
                $this->addError($file, self::CODE_PHP_RETURN_NOT_THIS, $parentClass, $lines);
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

    /**
     * Get parent class name
     *
     * @param string $class
     * @param string $content
     * @return null|string
     */
    protected function getClassAlias($class, $content)
    {
        $matches = array();
        if (strpos($class, '\\')) {
            if (0 === strpos($class, '\\')) {
                //ignore absolute class path
                return null;
            }
            $parentPath = substr($class, 0, strpos($class, "\x5C"));
            if (preg_match('~use ([A-z0-9\x5C_]+)\x5C'.$parentPath.';~', $content, $matches)
                || preg_match('~use ([A-z0-9\x5C_]+) as '.$parentPath.';~', $content, $matches)
            ) {
                return $matches[1].substr($class, strpos($class, "\x5C"));
            }
        }

        $class   = ltrim($class, '\\');
        $matched = null;

        preg_match('~use [\x5C]?'.$class.' as ([A-z0-9_]+);~', $content, $matches);
        if (!empty($matches[1])) {
            $matched = $matches[1];
        }

        if ($matched === null) {
            preg_match('~use ([A-z0-9\x5C_]+[\x5C]'.$class.');~', $content, $matches);
            if (!empty($matches[1])) {
                $matched = $matches[1];
            }
        }

        if ($matched === null) {
            preg_match('~use ([A-z0-9\x5C_]+) as '.$class.';~', $content, $matches);
            if (!empty($matches[1])) {
                $matched = $matches[1];
            }
        }

        return $matched;
    }
}
