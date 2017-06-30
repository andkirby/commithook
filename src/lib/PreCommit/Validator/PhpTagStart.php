<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */

namespace PreCommit\Validator;

/**
 * Class validator to check PHP start tag
 *
 * @package PreCommit\Validator
 */
class PhpTagStart extends AbstractValidator
{
    /**#@+
     * Error codes
     */
    const CODE_PHP_TAG = 'noPhpTagStart';
    /**#@-*/

    /**
     * Error messages
     *
     * @var array
     */
    protected $errorMessages
        = array(
            self::CODE_PHP_TAG => 'File does not start with php opening tag. Any preceding rows may start output.',
        );

    /**
     * Checking for interpreter errors
     *
     * @param string $content Absolute path
     * @param string $file
     * @return bool
     */
    public function validate($content, $file)
    {
        $this->validatePhpOpenTag($content, $file);

        return !$this->errorCollector->hasErrors();
    }

    /**
     * Check opened PHP tag in the beginning of a file
     *
     * @param string $content
     * @param string $file
     * @return $this
     */
    protected function validatePhpOpenTag($content, $file)
    {
        if (0 !== strpos($content, '<?')) {
            $this->addError($file, self::CODE_PHP_TAG);
        }

        return $this;
    }
}
