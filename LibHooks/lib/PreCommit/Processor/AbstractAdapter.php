<?php
namespace PreCommit\Processor;

use PreCommit\Exception;
use PreCommit\Processor\ErrorCollector as Error;
use \PreCommit\Vcs\AdapterInterface;

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
    protected $errorCollector;

    /**
     * Version Control System adapter
     *
     * @var \PreCommit\Vcs\AdapterInterface
     */
    protected $vcsAdapter;

    /**
     * Used validators list
     *
     * @var array
     */
    protected $validators = array();

    /**
     * Used filters list
     *
     * @var array
     */
    protected $filters = array();

    /**
     * Event observers
     *
     * 'event_name' => array(observer..n)
     *
     * @var array
     */
    protected $eventObservers = array();

    //endregion

    /**
     * Set default error collector
     *
     * @param string|array $options
     * @throws Exception
     */
    public function __construct($options = array())
    {
        if (is_string($options)) {
            $this->vcsAdapter = $this->getVcsAdapter($options);
        } elseif (is_object($options) && $options instanceof AdapterInterface) {
            $this->vcsAdapter = $options;
        } elseif (isset($options['vcs']) && is_object($options['vcs'])
                  && $options['vcs'] instanceof AdapterInterface
        ) {
            $this->vcsAdapter = $options['vcs'];
        } else {
            throw new Exception('VCS adapter is not set.');
        }

        if (is_array($options) && isset($options['errorCollector'])) {
            $this->errorCollector = $options['errorCollector'];
        } else {
            $this->errorCollector = $this->getErrorCollector();
        }
    }

    /**
     * Get VCS object
     *
     * @param string $type
     * @return string
     */
    protected function getVcsAdapter($type)
    {
        $type  = ucfirst($type);
        $class = 'PreCommit\\Vcs\\'.$type;

        return new $class();
    }

    /**
     * Get error collector
     *
     * @return ErrorCollector
     */
    protected function getErrorCollector()
    {
        return new Error();
    }

    /**
     * Process method
     *
     * @return mixed
     * @throws \PreCommit\Exception
     */
    abstract public function process();

    /**
     * Get errors output
     *
     * @return string
     */
    public function getErrorsOutput()
    {
        $output = '';
        foreach ($this->getErrors() as $file => $fileErrors) {
            $decorLength = 30 - strlen($file) / 2;
            $decorLength = $decorLength > 2 ? $decorLength : 3; //minimal decor line "==="
            $output .= str_repeat('=', round($decorLength - 0.1))
                       ." $file ".str_repeat('=', round($decorLength)).PHP_EOL;
            foreach ($fileErrors as $errorsType) {
                foreach ($errorsType as $error) {
                    $output .= str_replace(array("\n", PHP_EOL), '', $error['message'])."\n";
                }
            }
        }

        return $output;
    }

    /**
     * Get errors
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errorCollector->getErrors();
    }

    /**
     * Add "end"
     *
     * @param string   $event
     * @param \Closure $observer
     * @return $this
     */
    public function addObserver($event, \Closure $observer)
    {
        $this->eventObservers[$event][] = $observer;

        return $this;
    }

    /**
     * Dispatch event
     *
     * @param string            $event
     * @param array|string|null $params
     * @return $this
     */
    public function dispatchEvent($event, $params = null)
    {
        if (!empty($this->eventObservers[$event])) {
            /** @var \Closure $observer */
            foreach ($this->eventObservers[$event] as $observer) {
                $observer($this, $params);
            }
        }

        return $this;
    }

    /**
     * Load validator
     *
     * @param string $name
     * @param array  $options
     * @return \PreCommit\Validator\AbstractValidator
     */
    protected function loadValidator($name, array $options = array())
    {
        if (empty($this->validators[$name])) {
            $class                   = '\\PreCommit\\Validator\\'.str_replace('-', '\\', $name);
            $options                 = array_merge($this->getValidatorDefaultOptions(), $options);
            $this->validators[$name] = new $class($options);
        }

        return $this->validators[$name];
    }

    /**
     * Get default options for validators
     *
     * Added Error Collector by default
     *
     * @return array
     */
    protected function getValidatorDefaultOptions()
    {
        return array('errorCollector' => $this->errorCollector);
    }

    /**
     * Load filter
     *
     * @param string $name
     * @param array  $options
     * @return \PreCommit\Message\FilterInterface
     */
    protected function loadFilter($name, array $options = array())
    {
        if (empty($this->filters[$name])) {
            $class                = "\\PreCommit\\Filter\\$name";
            $this->filters[$name] = new $class($options);
        }

        return $this->filters[$name];
    }
}
