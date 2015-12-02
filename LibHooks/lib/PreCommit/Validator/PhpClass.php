<?php
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
    protected $_interpreterPath;

    /**
     * Constructor. Set path to PHP interpreter
     *
     * @param array $options
     * @throws Exception
     */
    public function __construct(array $options)
    {
        $interpreter = (string) Config::getInstance()->getNode('validators/PhpClass/interpret');
        if (empty($interpreter)) {
            throw new Exception('Path to PHP interpreter is not set.');
        }
        $this->_interpreterPath = $interpreter;

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
        $this->_validatePhpOpenedTag($content, $file);
        $filePath = func_get_arg(2);
        $this->_validatePhpByInterpret($filePath, $file);

        return !$this->errorCollector->hasErrors();
    }

    /**
     * Check opened PHP tag in the beginning of a file
     *
     * @param string $content
     * @param string $file
     * @return $this
     */
    protected function _validatePhpOpenedTag($content, $file)
    {
        if (0 !== strpos($content, '<?')) {
            $this->_addError($file, self::CODE_PHP_TAG);
        }

        return $this;
    }

    /**
     * Validate content by PHP interpret
     *
     * @param string $filePath
     * @param string $file
     * @return $this
     */
    protected function _validatePhpByInterpret($filePath, $file)
    {
        $exe = "{$this->_interpreterPath} -l $filePath 2>&1";
        exec($exe, $output, $code);
        if ($code != 0) {
            $value = trim(implode(" ", str_replace($filePath, $file, $output)));
            $this->_addError(
                $file,
                self::CODE_PHP_INTERPRET,
                array(
                    'path'  => $this->_interpreterPath,
                    'value' => $value,
                )
            );
        }

        return $this;
    }
}
