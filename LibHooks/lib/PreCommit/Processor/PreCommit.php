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

            $content = $this->_loadFilter('SkipContent')->filter($content, $file);

            $validators = $this->getValidators($ext);
            foreach ($validators as $validatorName) {
                $this->_loadValidator($validatorName)
                    ->validate($content, $file, $filePath);
            }

            //for all files
            $this->_loadValidator('TrailingSpace')
                ->validate($content, $file);

            $this->_loadValidator('FileStyle')
                ->validate($content, $file);
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
}
