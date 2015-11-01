<?php
namespace PreCommit\Processor;

use PreCommit\Config as Config;
use PreCommit\Exception as Exception;
use PreCommit\Filter\InterfaceFilter as InterfaceFilter;

/**
 * Class abstract process adapter
 *
 * @package PreCommit\Processor
 * @method InterfaceFilter _loadFilter
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
     *
     * @param array|string $vcsType
     * @throws \PreCommit\Exception
     */
    public function __construct($vcsType)
    {
        parent::__construct($vcsType);
        $this->setCodePath($this->_vcsAdapter->getCodePath());
        $this->setFiles($this->_vcsAdapter->getAffectedFiles());
    }

    //region GettersSetters
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
    //endregion

    /**
     * Process code
     *
     * @return bool
     * @throws Exception
     */
    public function process()
    {
        if (!$this->_files) {
            return true;
        }

        if (!$this->_canProcess()) {
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
            $content  = $this->_getFileContent($filePath);
            $ext      = pathinfo($file, PATHINFO_EXTENSION);

            //run validators for non-filtered content
            $this->runValidators('before_all_original', $content, $file, $filePath);

            //run filters and validators before running by the file extension
            $content = $this->runFilters('before_all', $content, $file, $filePath);
            $this->runValidators('before_all', $content, $file, $filePath);

            $content = $this->runFilters($ext, $content, $file, $filePath);
            $this->runValidators($ext, $content, $file, $filePath);

            $content = $this->runFilters('after_all', $content, $file, $filePath);
            $this->runValidators('after_all', $content, $file, $filePath);
        }
        return !$this->_errorCollector->hasErrors();
    }

    /**
     * Can files processed
     *
     * In this method added checking commit message.
     * We need no to check "Revert" commits and "Merge branch".
     *
     * @return string
     */
    protected function _canProcess()
    {
        return !$this->_vcsAdapter->isMergeInProgress();
    }

    /**
     * Get file path
     *
     * @param string $file
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
     * Get file content
     *
     * @param string $filePath
     * @return string
     */
    protected function _getFileContent($filePath)
    {
        return file_get_contents($filePath);
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
    public function runValidators($ext, $content, $file, $filePath)
    {
        foreach ($this->getValidators($ext) as $validatorName => $status) {
            if ($status && $status !== 'false') {
                $this->_loadValidator($validatorName)
                    ->validate($content, $file, $filePath);
            }
        }
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
    public function runFilters($ext, $content, $file, $filePath)
    {
        foreach ($this->getFilters($ext) as $validatorName => $status) {
            if ($status && $status !== 'false') {
                $content = $this->_loadFilter($validatorName)
                    ->filter($content, $file, $filePath);
            }
        }
        return $content;
    }

    /**
     * Get validators by file type
     *
     * @param string $fileType
     * @return array
     */
    public function getValidators($fileType)
    {
        return $this->_getConfig()->getNodeArray('hooks/pre-commit/filetype/' . $fileType . '/validators');
    }

    /**
     * Get filters by file type
     *
     * @param string $fileType
     * @return array
     */
    public function getFilters($fileType)
    {
        return $this->_getConfig()->getNodeArray('hooks/pre-commit/filetype/' . $fileType . '/filters');
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
}
