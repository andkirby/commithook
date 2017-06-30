<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */

namespace PreCommit\Validator;

use PreCommit\Config;
use PreCommit\Exception;

/**
 * Class validator for check PHP interpreter errors
 *
 * @package PreCommit\Validator
 */
class PhpClass extends AbstractValidator
{
    /**#@+
     * Error codes
     */
    const CODE_PHP_TAG       = 'noPhpTagStart';
    const CODE_PHP_INTERPRET = 'phpInterpret';
    /**#@-*/

    /**
     * Error messages
     *
     * @var array
     */
    protected $errorMessages
        = array(
            self::CODE_PHP_TAG       => 'File does not start with php opening tag. Any preceding rows may start output.',
            self::CODE_PHP_INTERPRET => "PHP interpreter (%path%) has found run-time errors! Check this: \n %value%",
        );

    /**
     * Path PHP interpreter
     *
     * @var string
     */
    protected $interpreterPath;

    /**
     * Constructor. Set path to PHP interpreter
     *
     * @param array $options
     * @throws Exception
     */
    public function __construct(array $options)
    {
        $interpreter = (string) Config::getInstance()->getNode('code/interpreter/php');
        if (empty($interpreter)) {
            throw new Exception('Path to PHP interpreter is not set.');
        }
        $this->interpreterPath = $interpreter;

        parent::__construct($options);
    }

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
        $filePath = func_get_arg(2);
        $this->validatePhp($filePath, $file);

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

    /**
     * Validate content by PHP interpreter
     *
     * @param string $filePath
     * @param string $file
     * @return $this
     */
    protected function validatePhp($filePath, $file)
    {
        $exe = "{$this->getInterpreter()} -l $filePath 2>&1";
        exec($exe, $output, $code);
        if ($code != 0) {
            $value = trim(implode(" ", str_replace($filePath, $file, $output)));
            $this->addError(
                $file,
                self::CODE_PHP_INTERPRET,
                array(
                    'path'  => $this->getInterpreter(),
                    'value' => $value,
                )
            );
        }

        return $this;
    }

    /**
     * Get path to interpreter binary file
     *
     * @return string
     */
    protected function getInterpreter()
    {
        return $this->interpreterPath;
    }
}
