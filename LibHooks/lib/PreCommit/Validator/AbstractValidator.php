<?php
namespace PreCommit\Validator;

use PreCommit\Exception as Exception;
use PreCommit\Processor\ErrorCollector as Error;

/**
 * Class AbstractValidator
 *
 * @package PreCommit\Validator
 */
abstract class AbstractValidator
{
    /**
     * Extra Options
     *
     * @var array
     */
    protected $options = array();

    /**
     * Error collector
     *
     * @var Error
     */
    protected $errorCollector = array();

    /**
     * Error messages
     *
     * @var array
     */
    protected $errorMessages = array();

    /**
     * @param array $options
     * @throws Exception
     */
    public function __construct(array $options)
    {
        if (isset($options['errorCollector']) && $options['errorCollector'] instanceof Error) {
            $this->errorCollector = $options['errorCollector'];
            unset($options['errorCollector']);
        } else {
            throw new Exception('Error collector undefined.');
        }
        $this->options = $options;
    }

    /**
     * Validate method
     *
     * @param string $content
     * @param string $file    Validated file
     * @return bool
     */
    abstract public function validate($content, $file);

    /**
     * Add error
     *
     * @param string $file
     * @param string $type
     * @param string $value
     * @param int    $line
     * @return $this
     */
    protected function _addError($file, $type, $value = null, $line = null)
    {
        $this->errorCollector->addError($file, $type, $this->errorMessages[$type], $value, $line);
    }
}
