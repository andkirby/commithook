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

        $fileFilter = new Validator\FileFilter();

        foreach ($this->_files as $file) {
            $file = trim($file);

            if (!$fileFilter->validate('', $file)) {
                //file ignored for processing
                continue;
            }

            $filePath = $this->_getFilePath($file);
            $content = file_get_contents($filePath);
            $ext = pathinfo($file, PATHINFO_EXTENSION);
            switch ($ext) {
                case 'php':
                    $this->_loadValidator('PhpClass')
                        ->validate($content, $file, $filePath);

                    $this->_loadValidator('PhpDoc')
                        ->validate($content, $file);

                    $this->_loadValidator('CodingStandard')
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
            $fileStyle = $this->_loadValidator('FileStyle');;
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
}





