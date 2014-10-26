<?php
namespace PreCommit\Processor;
use \PreCommit\Exception as Exception;
use \PreCommit\Config as Config;

/**
 * Class abstract process adapter
 *
 * @package PreCommit\Processor
 */
class PreCommit extends AbstractAdapter
{
    //region Properties
    /**
     * List of files which should be validated
     *
     * @var array
     */
    protected $_files = array();

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
    //endregion

    /**
     * Set adapter data from config
     */
    public function __construct($vcsType)
    {
        parent::__construct($vcsType);
        $this->setCodePath($this->_vcsAdapter->getCodePath());
        $this->setFiles($this->_vcsAdapter->getAffectedFiles());
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
    //endregion

    /**
     * @return bool
     * @throws Exception
     */
    public function process()
    {
        if (!$this->_files) {
            return true;
        }

        if (!$this->_canProcessed()) {
            return true;
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

            //run validators for non-filtered content
            $this->runValidatorsByExtension('before_all_original', $content, $file, $filePath);

            //run filters and validators before running by the file extension
            $content = $this->runFiltersByExtension('before_all', $content, $file, $filePath);
            $this->runValidatorsByExtension('before_all', $content, $file, $filePath);

            $content = $this->runFiltersByExtension($ext, $content, $file, $filePath);
            $this->runValidatorsByExtension($ext, $content, $file, $filePath);

            $content = $this->runFiltersByExtension('after_all', $content, $file, $filePath);
            $this->runValidatorsByExtension('after_all', $content, $file, $filePath);
        }
        return array() == $this->_errorCollector->getErrors();
    }

    /**
     * Get file path
     *
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
     * Can files processed
     *
     * In this method added checking commit message.
     * We need no to check "Revert" commits and "Merge branch".
     *
     * @return string
     */
    protected function _canProcessed()
    {
        return !$this->_vcsAdapter->isMergeInProgress();
    }

    /**
     * Get config model
     *
     * @return Config
     * @throws \PreCommit\Exception
     */
    protected function _getConfig()
    {
        return Config::getInstance();
    }

    /**
     * Get validators by file type
     *
     * @param string $fileType
     * @return array
     */
    public function getValidators($fileType)
    {
        $array = $this->_getConfig()->getNodeArray('hooks/pre-commit/filetype/' . $fileType . '/validators');
        return array_keys($array);
    }

    /**
     * Get filters by file type
     *
     * @param string $fileType
     * @return array
     */
    public function getFilters($fileType)
    {
        $array = $this->_getConfig()->getNodeArray('hooks/pre-commit/filetype/' . $fileType . '/filters');
        return array_keys($array);
    }

    /**
     * Run filters gotten by file extension or some key
     *
     * @param string $ext
     * @param string $content
     * @param string $file
     * @param string $filePath
     * @return string           Return filtered content
     */
    public function runFiltersByExtension($ext, $content, $file, $filePath)
    {
        foreach ($this->getFilters($ext) as $validatorName) {
            $content = $this->_loadFilter($validatorName)
                ->filter($content, $file, $filePath);
        }
        return $content;
    }

    /**
     * Run validators gotten by file extension or some key
     *
     * @param string $ext
     * @param string $content
     * @param string $file
     * @param string $filePath
     * @return void             Returns nothing
     */
    public function runValidatorsByExtension($ext, $content, $file, $filePath)
    {
        foreach ($this->getValidators($ext) as $validatorName) {
            $content = $this->_loadValidator($validatorName)
                ->validate($content, $file, $filePath);
        }
    }
}
