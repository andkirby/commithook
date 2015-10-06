<?php
namespace PreCommit\Composer\Command\Config;

use PreCommit\Composer\Command\CommandAbstract;
use PreCommit\Composer\Exception;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use PreCommit\Composer\Command\Helper;

/**
 * CommitHooks command tester
 *
 * It will test all modified files
 *
 * @package PreCommit\Composer
 */
class Set extends CommandAbstract
{
    /**#@+
     * Option scopes
     *
     * project-self: ~/.commithook/projects/PROJECT_NAME/commithook.xml
     * project:      PROJECT_DIR/commithook.xml
     * global:       ~/.commithook/commithook.xml
     */
    const OPTION_SCOPE_GLOBAL = 'global';
    const OPTION_SCOPE_PROJECT = 'project';
    const OPTION_SCOPE_PROJECT_SELF = 'project-self';
    /**#@-*/

    /**
     * Tracker type XML path
     */
    const XPATH_TRACKER_TYPE = 'tracker/type';

    /**
     * Default options
     *
     * @var array
     */
    protected $defaultOptions
        = array(
            'tracker',
            'url',
            'username',
            'password',
        );

    /**
     * Issues tracker type
     *
     * @var
     */
    protected $_trackerType;

    /**
     * Update status
     *
     * It will true if some file updated
     *
     * @var bool
     */
    protected $_updated = false;

    /**
     * Execute command
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return int
     * @throws Exception
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        try {
            $this->writeDefaultOptions($input, $output);
            $this->writeKeyValueOption($input, $output);
        } catch (Exception $e) {
            if ($this->isDebug($output)) {
                throw $e;
            } else {
                $output->writeln($e->getMessage());
                return 1;
            }
        }

        if ($this->_updated) {
            $output->writeln(
                "Configuration updated."
            );
        }
        return 0;
    }

    /**
     * Write default options
     *
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @throws \PreCommit\Composer\Exception
     */
    protected function writeDefaultOptions(InputInterface $input, OutputInterface $output, $readAll = false)
    {
        foreach ($this->defaultOptions as $name) {
            $value = $input->getOption($name);
            if (!$readAll && null === $value) {
                continue;
            }
            $xpath = $this->getXpath($input, $output, $name);
            $scope = $this->getScope($input, $output, $xpath);
            $this->writeConfig($input, $output, $xpath, $scope, $value);
        }
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param string                                            $name
     * @return string
     * @throws \PreCommit\Composer\Exception
     */
    protected function getXpath(InputInterface $input, OutputInterface $output, $name)
    {
        if (!$name) {
            throw new Exception('Empty config name.');
        }
        if ($this->isNameXpath($input)) {
            return $name;
        }
        switch ($name) {
            case 'password':
            case 'username':
            case 'project':
            case 'url':
                $name = $this->getTrackerType($input, $output, false) . '/' . $name;
                break;

            case 'tracker':
                $name = self::XPATH_TRACKER_TYPE;
                break;

            case 'exclude-extension':
            case 'skip-ext':
                $name = 'validators/FileFilter/filter/skip/path';
                break;

            case 'exclude-path':
            case 'skip-path':
                $name = 'validators/FileFilter/filter/skip/extensions';
                break;

            case 'task':
                $name = 'tracker/' . $this->getTrackerType($input, $output) . '/active_task';
                break;

            default:
                throw new Exception("Unknown config name '$name'.");
        }
        return $name;
    }

    /**
     * Check if name option is XML path
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @return mixed
     */
    protected function isNameXpath(InputInterface $input)
    {
        return (bool)$input->getOption('xpath');
    }

    /**
     * Get tracker type
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param bool            $ask
     * @return null|string
     */
    protected function getTrackerType(InputInterface $input, OutputInterface $output, $ask = true)
    {
        if ($this->_trackerType) {
            return $this->_trackerType;
        }
        if ($ask) {
            $this->_trackerType = $this->getQuestionHelper()->ask(
                $input, $output,
                $this->getSimpleQuestion()->getQuestion(
                    'Set issue tracker', null,
                    $this->getXpathOptions($input, $output, self::XPATH_TRACKER_TYPE)
                )
            );
            $url                = $this->getQuestionHelper()->ask(
                $input, $output,
                $this->getSimpleQuestion()->getQuestion(
                    "Set tracker URL ({$this->_trackerType})", null
                )
            );
            $scope              = $this->getScope($input, $output, self::XPATH_TRACKER_TYPE);
            $this->writeConfig($input, $output, self::XPATH_TRACKER_TYPE, $scope, $this->_trackerType);
            $this->writeConfig($input, $output, $this->_trackerType . '/url', $scope, $url);
        }
        if (!$this->_trackerType) {
            new Exception('Tracker type is not set. Please use command: commithook config --tracker [TRACKER]');
        }
        return $this->_trackerType;
    }

    /**
     * Get XML path input options
     *
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param string                                            $xpath
     * @return array
     */
    protected function getXpathOptions(InputInterface $input, OutputInterface $output, $xpath)
    {
        switch ($xpath) {
            case self::XPATH_TRACKER_TYPE:
                $values = array_values($this->getConfig()->getNodeArray('tracker/available_type'));
                break;

            default:
                return array();
        }
        $keys = array_keys(array_fill(1, count($values), 1));
        return array_combine($keys, $values);
    }

    /**
     * Get XML path input options
     *
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param string                                            $xpath
     * @return array
     */
    protected function getScope(InputInterface $input, OutputInterface $output, $xpath)
    {
        $type = null;
        if (self::XPATH_TRACKER_TYPE !== $xpath) {
            $type = $this->getTrackerType($input, $output, false);
        }
        $options = array(
            1 => self::OPTION_SCOPE_GLOBAL,
            2 => self::OPTION_SCOPE_PROJECT,
            3 => self::OPTION_SCOPE_PROJECT_SELF,
        );
        switch ($xpath) {
            case 'tracker/' . $type . '/active_task':
                return self::OPTION_SCOPE_PROJECT_SELF;
                break;
            case '' . $type . '/project':
                return self::OPTION_SCOPE_PROJECT;
                break;

            case self::XPATH_TRACKER_TYPE:
            case '' . $type . '/url':
                $default = 1;

                break;

            case '' . $type . '/username':
            case '' . $type . '/password':
                $default = 1;
                $options = array(
                    1 => self::OPTION_SCOPE_GLOBAL,
                    3 => self::OPTION_SCOPE_PROJECT_SELF,
                );
                break;

            default:
                $default = 3;
                break;
        }

        $scope = $this->getScopeOption($input, $output);
        if ($scope && in_array($scope, $options)) {
            return $scope;
        }

        return $this->getQuestionHelper()->ask(
            $input, $output,
            $this->getSimpleQuestion()->getQuestion(
                "Set config scope ($xpath)", $default,
                $options
            )
        );
    }

    /**
     * Get scope option
     *
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return null|string
     */
    protected function getScopeOption(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption(self::OPTION_SCOPE_GLOBAL)) {
            return self::OPTION_SCOPE_GLOBAL;
        }
        if ($input->getOption(self::OPTION_SCOPE_PROJECT)) {
            return self::OPTION_SCOPE_PROJECT;
        }
        if ($input->getOption(self::OPTION_SCOPE_PROJECT_SELF)) {
            return self::OPTION_SCOPE_PROJECT_SELF;
        }
        return null;
    }

    /**
     * Write config
     *
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param string                                            $xpath
     * @param string                                            $scope
     * @param string                                            $value
     * @return $this
     * @throws Exception
     */
    protected function writeConfig(InputInterface $input, OutputInterface $output, $xpath, $scope, $value)
    {
        $result = $this->getConfigHelper()->writeValue(
            $this->getConfigFile($scope), $xpath, $value
        );

        if (self::XPATH_TRACKER_TYPE === $xpath) {
            $this->_trackerType = $value;
        }

        $this->_updated = $result ?: $this->_updated;
        return $this;
    }

    /**
     * Get config helper
     *
     * @return Helper\Config
     */
    protected function getConfigHelper()
    {
        return $this->getHelperSet()->get(Helper\Config::NAME);
    }

    /**
     * Get config file related to scope
     *
     * @param $scope
     * @return null|string
     * @throws \PreCommit\Exception
     */
    protected function getConfigFile($scope)
    {
        if (self::OPTION_SCOPE_GLOBAL == $scope) {
            return $this->getConfig()->getConfigFile('userprofile');
        } elseif (self::OPTION_SCOPE_PROJECT == $scope) {
            return $this->getConfig()->getConfigFile('project');
        } elseif (self::OPTION_SCOPE_PROJECT_SELF == $scope) {
            return $this->getConfig()->getConfigFile('project_local');
        }
        throw new \PreCommit\Exception("Unknown scope '$scope'.");
    }

    /**
     * Write key-value option
     *
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return $this
     * @throws \PreCommit\Composer\Exception
     */
    protected function writeKeyValueOption(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getOption('name')) {
            return $this;
        }
        $xpath = $this->getXpath($input, $output, $input->getOption('name'));
        $value = $this->getValue($input, $output, $xpath);
        $scope = $this->getScope($input, $output, $xpath);
        $this->writeConfig($input, $output, $xpath, $scope, $value);
        return $this;
    }

    /**
     * Get value
     *
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param string                                            $xpath
     * @return string
     */
    protected function getValue(InputInterface $input, OutputInterface $output, $xpath)
    {
        if (!$input->getOption('value')) {
            $question = $this->getSimpleQuestion()->getQuestion(
                "Set value for XPath '$xpath'",
                false === strpos($xpath, 'password') ?
                    $this->getConfig()->getNode($xpath) : null
            );

            /**
             * Ask value without showing input for passwords
             */
            if (false !== strpos($xpath, 'password')) {
                $question->setHidden(true);
                $question->setHiddenFallback(true);
            }

            return $this->getQuestionHelper()->ask(
                $input, $output, $question
            );
        }
        return $input->getOption('value');
    }

    /**
     * @inheritDoc
     */
    public function setApplication(Application $application = null)
    {
        parent::setApplication($application);
        $this->getHelperSet()->set(new Helper\Config());
        $this->getHelperSet()->set(new Helper\Config\Writer());
    }

    /**
     * Init default helpers
     *
     * @return $this
     */
    protected function configureCommand()
    {
        $this->setName('config');

        $this->setHelp(
            'This command can set CommitHook configuration.'
        );
        $this->setDescription(
            'This command can set CommitHook configuration.'
        );
        return $this;
    }

    /**
     * Init input definitions
     *
     * @return $this
     */
    protected function configureInput()
    {
        parent::configureInput();
        $this->addOption(
            'name', '-k', InputOption::VALUE_REQUIRED,
            'Config name/XPath.'
        );
        /**
         * It will asked if it's omitted
         */
        $this->addOption(
            'value', '-l', InputOption::VALUE_OPTIONAL,
            'Config value.'
        );

        /**
         * When this parameter set key must be an XML path
         */
        $this->addOption(
            'xpath', '-x', InputOption::VALUE_NONE,
            'XPath mode.'
        );

        /**
         * Scope options
         */
        $this->addOption(
            'global', '-g', InputOption::VALUE_NONE,
            'Save config in global configuration file.'
        );
        $this->addOption(
            'project-self', '-s', InputOption::VALUE_NONE,
            'Save config in project private(!) configuration file. PROJECT_DIR/commithook-self.xml'
        );
        $this->addOption(
            'project', '-P', InputOption::VALUE_NONE,
            'Save config in project configuration file. PROJECT_DIR/commithook.xml'
        );

        foreach ($this->defaultOptions as $name) {
            $this->addOption(
                $name, null, InputOption::VALUE_OPTIONAL,
                "Tracker connection '$name' option."
            );
        }

        return $this;
    }
}
