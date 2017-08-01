<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
namespace PreCommit\Processor;

use PreCommit\Exception;
use PreCommit\Processor\ErrorCollector as Error;
use PreCommit\Vcs\AdapterInterface;

/**
 * Class abstract process adapter
 *
 * @package PreCommit\Processor
 */
abstract class AbstractAdapter
{
    //region Properties
    /**
     * Version Control System adapter
     *
     * @var \PreCommit\Vcs\AdapterInterface
     */
    protected static $vcsAdapter;

    /**
     * Current work directory
     *
     * @var string
     */
    protected static $cwd;

    /**
     * Error collector
     *
     * @var \PreCommit\Processor\ErrorCollector
     */
    protected $errorCollector;

    /**
     * Used validators list
     *
     * @var array
     */
    protected $validators = [];

    /**
     * Used filters list
     *
     * @var array
     */
    protected $filters = [];

    /**
     * Event observers
     *
     * 'event_name' => array(observer..n)
     *
     * @var array
     */
    protected $eventObservers = [];

    //endregion

    /**
     * Set default error collector
     *
     * @param string|AdapterInterface|array $options
     * @throws Exception
     */
    public function __construct($options = [])
    {
        if (null === self::$vcsAdapter) {
            static::$vcsAdapter = $this->initVcsAdapter($options);
        }

        if (is_array($options) && isset($options['errorCollector'])) {
            $this->errorCollector = $options['errorCollector'];
        } else {
            $this->errorCollector = $this->getErrorCollector();
        }
    }

    /**
     * Get current work directory
     *
     * @return string
     */
    public static function getCwd()
    {
        if (self::$cwd === null) {
            self::$cwd = getcwd();
        }

        return self::$cwd;
    }

    /**
     * Get VCS object
     *
     * @return AdapterInterface
     */
    public static function getVcsAdapter()
    {
        return static::$vcsAdapter;
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
            $start       = str_repeat('=', round($decorLength - 0.1));
            $end         = str_repeat('=', round($decorLength));
            $output .= $start." $file ".$end.PHP_EOL;
            foreach ($fileErrors as $errorsType) {
                foreach ($errorsType as $error) {
                    $output .= str_replace(["\n\r"], "\n", $error['message'])."\n";
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
     * Init VCS from string
     *
     * @param array|string $options
     * @return AbstractAdapter
     */
    protected function initVcsFromString($options)
    {
        if (strpos($options, '\\') || strpos($options, '_')) {
            $class = $options;
        } else {
            $class = '\\PreCommit\\Vcs\\'.ucfirst($options);
        }

        return new $class($options);
    }

    /**
     * Init VCS adapter
     *
     * @param string|AdapterInterface|array $options
     * @return mixed AdapterInterface
     * @throws Exception
     */
    protected function initVcsAdapter($options)
    {
        $vcsAdapter = null;
        if (is_object($options) && $options instanceof AdapterInterface) {
            $vcsAdapter = $options;
        } elseif (is_string($options)) {
            $vcsAdapter = $this->initVcsFromString($options);
        } elseif (is_array($options)) {
            if (isset($options['vcs']) && is_string($options['vcs'])) {
                $vcsAdapter = $this->initVcsFromString($options['vcs']);
            } elseif (isset($options['vcs']) && is_object($options['vcs'])
                      && $options['vcs'] instanceof AdapterInterface
            ) {
                $vcsAdapter = $options['vcs'];
            }
        }

        if (!$vcsAdapter) {
            throw new Exception('VCS adapter is not set.');
        }

        if (!empty($options['vcsFiles'])) {
            //set custom affected files
            $vcsAdapter->setAffectedFiles($options['vcsFiles']);
        }

        return $vcsAdapter;
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
     * Load validator
     *
     * @param string $name
     * @param array  $options
     * @return \PreCommit\Validator\AbstractValidator
     */
    protected function loadValidator($name, array $options = [])
    {
        if (empty($this->validators[$name])) {
            $class   = '\\PreCommit\\Validator\\'.str_replace('-', '\\', $name);
            $options = array_merge($this->getValidatorDefaultOptions(), $options);

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
        return ['errorCollector' => $this->errorCollector];
    }

    /**
     * Load filter
     *
     * @param string $name
     * @param array  $options
     * @return \PreCommit\Message\FilterInterface
     */
    protected function loadFilter($name, array $options = [])
    {
        if (empty($this->filters[$name])) {
            $class                = '\\PreCommit\\Filter\\'.str_replace('-', '\\', $name);
            $this->filters[$name] = new $class($options);
        }

        return $this->filters[$name];
    }
}
