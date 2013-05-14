<?php
namespace PreCommit;

use \PreCommit\Processor\ErrorCollector as Error;

/**
 * Class Processor. Input point for validate files
 * @package PreCommit
 */
class Processor
{
    //region Properties
    /**
     * List of files which should be validated
     *
     * @var array
     */
    protected $_files = array();

    /**
     * List of ignored files
     *
     * @var array
     */
    protected $_ignoredFiles = array();

    /**
     * Path to root of code
     *
     * @var string
     */
    protected $_codePath;

    /**
     * Path to PHP interpreter
     *
     * @var string
     */
    protected $_phpInterpreterPath;

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

    public function __construct()
    {
        $this->_errorCollector = new Error();
    }

    //region GettersSetters
    /**
     * Set files for validation
     *
     * @param array $files
     * @return $this
     */
    public function setFiles(array $files)
    {
        $this->_files = $files;
        return $this;
    }

    /**
     * Set code path
     *
     * @param string $codePath
     * @return $this
     */
    public function setCodePath($codePath)
    {
        $this->_codePath = $codePath;
        return $this;
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
    //endregion

    /**
     * @return bool
     * @throws Exception
     */
    public function process()
    {
        if (!$this->_files) {
            throw new Exception('Files list is empty.');
        }

        $fileFilter = $this->_loadValidator('FileFilter');

        foreach ($this->_files as $file) {
            $file = trim($file);

            if (!$fileFilter->validate('', $file)) {
                //file skipped for processing
                continue;
            }

            $filePath = $this->_getFilePath($file);
            $content = file_get_contents($filePath);
            $ext = pathinfo($file, PATHINFO_EXTENSION);
            switch ($ext) {
                case 'php':
                    $content = $this->_loadFilter('SkipContent')->filter($content);

                    $this->_loadValidator('PhpClass')
                        ->validate($content, $file, $filePath);

                    $this->_loadValidator('PhpDoc')
                        ->validate($content, $file);

                    $this->_loadValidator('CodingStandard')
                        ->validate($content, $file);

                    $this->_loadValidator('CodingStandardMagento')
                        ->validate($content, $file);
                    //no brake!

                case 'phtml':
                    $this->_loadValidator('Trailing')
                        ->validate($content, $file);
                    break;

                case 'xml':
                    $this->_loadValidator('Xml')
                        ->validate($content, $file);
                    break;
            }

            //for all files
            $fileStyle = $this->_loadValidator('FileStyle');
            $fileStyle->validate($content, $file);
        }
        return array() == $this->_errorCollector->getErrors();

    }

    /**
     * Load validator
     *
     * @param string $name
     * @param array $options
     * @return Validator\AbstractValidator
     */
    protected function _loadValidator($name, array $options = array())
    {
        if (empty($this->_validators[$name])) {
            $class = __NAMESPACE__ . "\\Validator\\$name";
            $options = array_merge($this->_getValidatorDefaultOptions(), $options);
            $this->_validators[$name] = new $class($options);
        }
        return $this->_validators[$name];
    }

    /**
     * Get default options for validators
     *
     * @return array
     */
    protected function _getValidatorDefaultOptions()
    {
        return array('errorCollector' => $this->_errorCollector);
    }

    /**
     * @param $file
     * @return string
     * @throws Exception
     */
    protected function _getFilePath($file)
    {
        $filePath = $this->_codePath . DIRECTORY_SEPARATOR . $file;
        if (!file_exists($filePath)) {
            throw new Exception("File '$filePath' does not exist.");
        }
        return $filePath;
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
            $decorLength = $decorLength > 2 ? $decorLength : 3;
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

    /**
     * Load filter
     *
     * @param string $name
     * @param array $options
     * @return Filter\InterfaceFilter
     */
    protected function _loadFilter($name, array $options = array())
    {
        if (empty($this->_filters[$name])) {
            $class = __NAMESPACE__ . "\\Filter\\$name";
            $this->_filters[$name] = new $class($options);
        }
        return $this->_filters[$name];
    }
}





