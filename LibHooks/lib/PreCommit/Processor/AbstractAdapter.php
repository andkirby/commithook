<?php
namespace PreCommit\Processor;
use \PreCommit\Processor\ErrorCollector as Error;

/**
 * Class abstract process adapter
 *
 * @package PreCommit\Processor
 */
abstract class AbstractAdapter
{
    //region Properties
    /**
     * Error collector
     *
     * @var \PreCommit\Processor\ErrorCollector
     */
    protected $_errorCollector;

    /**
     * Used validators list
     *
     * @var array
     */
    protected $_validators = array();

    /**
     * Used filters list
     *
     * @var array
     */
    protected $_filters = array();
    //endregion

    /**
     * Set default error collector
     */
    public function __construct()
    {
        $this->_errorCollector = new Error();
    }

    /**
     * Get errors
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->_errorCollector->getErrors();
    }

    /**
     * Process method
     *
     * @return mixed
     * @throws \PreCommit\Exception
     */
    abstract public function process();

    /**
     * Load validator
     *
     * @param string $name
     * @param array  $options
     * @return \PreCommit\Validator\AbstractValidator
     */
    protected function _loadValidator($name, array $options = array())
    {
        if (empty($this->_validators[$name])) {
            $class                    = "\\PreCommit\\Validator\\$name";
            $options                  = array_merge($this->_getValidatorDefaultOptions(), $options);
            $this->_validators[$name] = new $class($options);
        }
        return $this->_validators[$name];
    }

    /**
     * Get default options for validators
     *
     * Added Error Collector by default
     *
     * @return array
     */
    protected function _getValidatorDefaultOptions()
    {
        return array('errorCollector' => $this->_errorCollector);
    }

    /**
     * Load filter
     *
     * @param string $name
     * @param array  $options
     * @return \PreCommit\Filter\InterfaceFilter
     */
    protected function _loadFilter($name, array $options = array())
    {
        if (empty($this->_filters[$name])) {
            $class                 = "\\PreCommit\\Filter\\$name";
            $this->_filters[$name] = new $class($options);
        }
        return $this->_filters[$name];
    }

    /**
     * Get errors output
     *
     * @return string
     */
    public function getErrorsOutput()
    {
        $output = '';
        foreach ($this->getErrors() as $file => $fileErrors) {
            $decorLength = 60 - strlen($file) / 2;
            $decorLength = $decorLength > 2 ? $decorLength : 3; //minimal decor line "==="
            $output .= str_repeat('=', round($decorLength - 0.1))
                . " $file " . str_repeat('=', round($decorLength)) . PHP_EOL;
            foreach ($fileErrors as $errorsType) {
                foreach ($errorsType as $error) {
                    $output .= str_replace(array("\n", PHP_EOL), '', $error['message']) . "\n";
                }
            }
        }
        return $output;
    }
}
