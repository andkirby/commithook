<?php
namespace PreCommit\Validator;

use \PreCommit\Exception as Exception;
use \PreCommit\Processor\ErrorCollector as Error;

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
    protected $_options = array();

    /**
     * Error collector
     *
     * @var Error
     */
    protected $_errorCollector = array();

    /**
     * Error messages
     *
     * @var array
     */
    protected $_errorMessages = array();

    /**
     * @param array $options
     * @throws Exception
     */
    public function __construct(array $options)
    {
        if (isset($options['errorCollector']) && $options['errorCollector'] instanceof Error) {
            $this->_errorCollector = $options['errorCollector'];
            unset($options['errorCollector']);
        } else {
            throw new Exception('Error collector undefined.');
        }
        $this->_options = $options;
    }

    /**
     * Validate method
     *
     * @param string $content
     * @param string $file      Validated file
     * @return bool
     */
    abstract public function validate($content, $file);

    /**
     * Add error
     *
     * @param string $file
     * @param string $type
     * @param string $value
     * @param int $line
     * @return $this
     */
    protected function _addError($file, $type, $value = null, $line = null)
    {
        $this->_errorCollector->addError($file, $type, $this->_errorMessages[$type], $value, $line);
    }
}
