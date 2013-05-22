<?php
namespace PreCommit\Processor;
use \PreCommit\Exception as Exception;

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
    public function __construct()
    {
        $this->setCodePath(trim(`git rev-parse --show-toplevel`));
        $this->setFiles(array_filter(explode("\n", `git diff --cached --name-only --diff-filter=ACM`)));
        parent::__construct();

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
