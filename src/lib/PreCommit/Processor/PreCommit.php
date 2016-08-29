<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
namespace PreCommit\Processor;

use PreCommit\Config as Config;
use PreCommit\Console\Command\Config\IgnoreCommit;
use PreCommit\Console\Command\Config\Set;
use PreCommit\Console\Helper\ClearCacheHelper;
use PreCommit\Console\Helper\Config\WriterHelper;
use PreCommit\Console\Helper\ConfigHelper;
use PreCommit\Exception as Exception;
use PreCommit\Filter\FilterInterface;
use PreCommit\Helper\FileType;
use PreCommit\Validator as Validator;
use Symfony\Component\Console\Helper\HelperSet;

/**
 * Class abstract process adapter
 *
 * @package PreCommit\Processor
 * @method FilterInterface loadFilter
 */
class PreCommit extends AbstractAdapter
{
    //region Properties
    /**
     * List of files which should be validated
     *
     * @var array
     */
    protected $files = array();

    /**
     * Path to root of code
     *
     * @var string
     */
    protected $codePath;

    /**
     * Path to PHP interpreter
     *
     * @var string
     */
    protected $phpInterpreterPath;

    /**
     * Validators list which should be omitted
     *
     * It will have TRUE value for disabling all validators
     *
     * @var array|bool
     */
    protected $omittedValidators;

    /**
     * Helper set
     *
     * @var array
     */
    protected $helperSet;

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
        $this->setCodePath($this->getVcsAdapter()->getCodePath());
        $this->setFiles($this->getVcsAdapter()->getAffectedFiles());

        $this->helperSet = new HelperSet();
        $this->initHelperSet();
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
        $this->codePath = $codePath;

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
        $this->files = $files;

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
        if (!$this->files) {
            return true;
        }

        if (!$this->canProcess()) {
            return true;
        }

        /** @var Validator\FileFilter $fileFilter */
        $fileFilter = $this->loadValidator('FileFilter');
        /** @var Validator\IgnoreContentFilter $ignoreContentFilter */
        $ignoreContentFilter = $this->loadValidator('IgnoreContentFilter');

        foreach ($this->files as $file) {
            $file = trim($file);

            if (!$fileFilter->validate('', $file)) {
                //file skipped for processing
                continue;
            }

            if (!$this->canProcessFile($file)) {
                continue;
            }

            $filePath = $this->getFilePath($file);
            $content  = $this->getFileContent($filePath);

            if (!$ignoreContentFilter->validate($content, $file)) {
                continue;
            }

            $ext = pathinfo($file, PATHINFO_EXTENSION);
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

        return !$this->errorCollector->hasErrors();
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
            if ((int) $status && $status !== 'false') {
                /** @noinspection PhpMethodParametersCountMismatchInspection */
                $this->loadValidator($validatorName)
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
                /** @noinspection PhpMethodParametersCountMismatchInspection */
                $content = $this->loadFilter($validatorName)
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
        return $this->getConfig()->getNodeArray('hooks/pre-commit/filetype/'.$fileType.'/validators');
    }

    /**
     * Get filters by file type
     *
     * @param string $fileType
     * @return array
     */
    public function getFilters($fileType)
    {
        return $this->getConfig()->getNodeArray('hooks/pre-commit/filetype/'.$fileType.'/filters');
    }

    /**
     * Get config helper
     *
     * @return ConfigHelper
     */
    public function getConfigHelper()
    {
        return $this->getHelperSet()->get(ConfigHelper::NAME);
    }

    /**
     * Can files processed
     *
     * In this method added checking commit message.
     * We need no to check "Revert" commits and "Merge branch".
     *
     * @return string
     */
    protected function canProcess()
    {
        return !$this->getVcsAdapter()->isMergeInProgress();
    }

    /**
     * Check if can process file
     *
     * @param string $file
     * @return array
     */
    protected function canProcessFile($file)
    {
        $type = new FileType();

        return in_array(
            pathinfo($file, PATHINFO_EXTENSION),
            $type->getFileTypes()
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function loadValidator($name, array $options = array())
    {
        $omitted = $this->getOmittedValidators();
        if (true === $omitted || isset($omitted[$name])) {
            $name = 'Stub';
        }

        return parent::loadValidator($name, $options);
    }

    /**
     * Get file path
     *
     * @param string $file
     * @return string
     * @throws Exception
     */
    protected function getFilePath($file)
    {
        $filePath = $this->codePath.DIRECTORY_SEPARATOR.$file;
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
    protected function getFileContent($filePath)
    {
        return file_get_contents($filePath);
    }

    /**
     * Get validators which should be ignored
     *
     * @return array|bool
     */
    protected function getOmittedValidators()
    {
        if (null === $this->omittedValidators) {
            $this->addBlindCommitModeRemoving();

            if ($this->isAllValidatorsDisabled()) {
                //ignore all validators (set TRUE for such mode)
                $this->omittedValidators = true;

                return $this->omittedValidators;
            }

            $this->omittedValidators = array();
            $types                   = array(
                'code'       => IgnoreCommit::XPATH_MODE_CODE,
                'protection' => IgnoreCommit::XPATH_MODE_PROTECTION,
            );
            foreach ($types as $type => $xpath) {
                //get validators set
                $validators = $this->getOmittedTypeValidators($xpath, $type);
                if ($validators) {
                    $this->omittedValidators = array_merge(
                        $this->omittedValidators,
                        $validators
                    );
                }
            }
        }

        return $this->omittedValidators;
    }

    /**
     * Get config file related to scope
     *
     * @param string $scope
     * @return null|string
     * @throws \PreCommit\Exception
     */
    protected function getConfigFile($scope)
    {
        if (Set::OPTION_SCOPE_GLOBAL == $scope) {
            return $this->getConfig()->getConfigFile('userprofile');
        } elseif (Set::OPTION_SCOPE_PROJECT == $scope) {
            return $this->getConfig()->getConfigFile('project');
        } elseif (Set::OPTION_SCOPE_PROJECT_SELF == $scope) {
            return $this->getConfig()->getConfigFile('project_local');
        }
        throw new Exception("Unknown scope '$scope'.");
    }

    /**
     * Get omitted type validators
     *
     * @param string $xpath
     * @param string $type
     * @return array
     */
    protected function getOmittedTypeValidators($xpath, $type)
    {
        $validators = array();
        if ($this->getConfig()->getNode($xpath)) {
            $validators = $this->getConfig()->getNodesExpr(
                sprintf(IgnoreCommit::XPATH_IGNORED_VALIDATORS, $type)
            );
        }

        return $validators;
    }

    /**
     * Get config model
     *
     * @return Config
     * @throws \PreCommit\Exception
     */
    protected function getConfig()
    {
        return Config::getInstance();
    }

    /**
     * Get helper set
     *
     * @return HelperSet
     */
    protected function getHelperSet()
    {
        return $this->helperSet;
    }

    /**
     * Init helper set
     *
     * @return $this
     */
    protected function initHelperSet()
    {
        $this->getHelperSet()->set(new ClearCacheHelper());

        $helper = new ConfigHelper();
        $helper->setWriter(new WriterHelper());
        $this->getHelperSet()->set($helper);

        return $this;
    }

    /**
     * Check if all validators are disabled
     *
     * @return bool
     */
    protected function isAllValidatorsDisabled()
    {
        return (bool) $this->getConfig()->getNode(IgnoreCommit::XPATH_MODE_ALL);
    }

    /**
     * Add observers to remove "blind-commit" flags
     *
     * @return $this
     * @throws \PreCommit\Exception
     */
    protected function addBlindCommitModeRemoving()
    {
        /** @var ConfigHelper $configHelper */
        $configHelper = $this->getConfigHelper();
        $configFile   = $this->getConfigFile(Set::OPTION_SCOPE_PROJECT_SELF);

        // @codingStandardsIgnoreStart
        //remove blind commit modes
        foreach (
            array(
                IgnoreCommit::XPATH_MODE_CODE,
                IgnoreCommit::XPATH_MODE_PROTECTION,
                IgnoreCommit::XPATH_MODE_ALL,
            ) as $xpath
        ) {
            //add observer to remove blind-commit
            $this->addObserver(
                'success_end',
                function () use ($configHelper, $configFile, $xpath) {
                    $configHelper->writeValue($configFile, $xpath, false);
                }
            );
        }

        // @codingStandardsIgnoreEnd

        return $this;
    }
}
