<?php
namespace PreCommit\Composer\Command\Config;

use PreCommit\Composer\Command\CommandAbstract;
use PreCommit\Composer\Exception;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use PreCommit\Composer\Command\Helper;
use Symfony\Component\Console\Question\Question;

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
     * Scope options
     *
     * A scope is associated with a particular configuration file.
     *
     * @var array
     */
    protected $scopeOptions
        = array(
            1 => self::OPTION_SCOPE_GLOBAL,
            2 => self::OPTION_SCOPE_PROJECT,
            3 => self::OPTION_SCOPE_PROJECT_SELF,
        );

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
            if ($input->getArgument('key') === 'wizard') {
                $this->connectionWizard($input, $output);

                if ($this->_updated) {
                    $output->writeln('Configuration updated.');
                    $output->writeln('Do not forget to share project commithook.xml file with your team.');
                    $output->writeln('Enjoy!');
                }
            } else {
                $this->writeDefaultOptions($input, $output);
                $this->writeKeyValueOption($input, $output);

                if ($this->_updated) {
                    $output->writeln(
                        'Configuration updated.'
                    );
                }
            }
        } catch (Exception $e) {
            if ($this->isDebug($output)) {
                throw $e;
            } else {
                $output->writeln($e->getMessage());
                return 1;
            }
        }
        return 0;
    }

    /**
     * Connection tracker wizard
     *
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return $this
     */
    protected function connectionWizard(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Set up issue tracker connection.');

        //type
        $options            = $this->getXpathOptions(self::XPATH_TRACKER_TYPE);
        $this->_trackerType = $this->getQuestionHelper()->ask(
            $input, $output,
            $this->getSimpleQuestion()->getQuestion(
                'Tracker type',
                array_search(
                    $this->getConfig()->getNode(self::XPATH_TRACKER_TYPE),
                    $options
                ),
                $options
            )
        );

        //URL
        $url = $this->getQuestionHelper()->ask(
            $input, $output,
            $this->getSimpleQuestion()->getQuestion(
                "'{$this->_trackerType}' URL",
                $this->getConfig()->getNode($this->getXpath('url'))
            )
        );

        //username
        $username = $this->getQuestionHelper()->ask(
            $input, $output,
            $this->getSimpleQuestion()->getQuestion(
                "'{$this->_trackerType}' username",
                $this->getConfig()->getNode($this->getXpath('username'))
            )
        );

        //password
        $question = $this->getSimpleQuestion()->getQuestion(
            "'{$this->_trackerType}' password",
            $this->getConfig()->getNode($this->getXpath('password'))
                ? '*****' : null
        );
        $question->setHiddenFallback(false);
        $question->setHiddenFallback(true);
        $password = $this->getQuestionHelper()->ask($input, $output, $question);
        $password = '*****' !== $password ? $password : null;

        //project key
        $prjKey = $this->getQuestionHelper()->ask(
            $input, $output,
            $this->getSimpleQuestion()->getQuestion(
                "Current '{$this->_trackerType}' project key",
                $this->getConfig()->getNode($this->getXpath('project'))
            )
        );

        $scope = $this->getScope($input, $output, self::XPATH_TRACKER_TYPE);

        $scopeCredentials = self::OPTION_SCOPE_PROJECT == $scope
            ? $this->getCredentialsScope($input, $output) : $scope;

        $this->writeConfig(self::XPATH_TRACKER_TYPE, $scope, $this->_trackerType);
        $this->writeConfig($this->getXpath('url'), $scope, $url);
        $this->writeConfig($this->getXpath('username'), $scopeCredentials, $username);
        if (null !== $password) {
            $this->writeConfig($this->getXpath('password'), $scopeCredentials, $password);
        }
        $this->writeConfig($this->getXpath('project'), self::OPTION_SCOPE_PROJECT, $prjKey);
        return $this;
    }

    /**
     * Get XML path input options
     *
     * @param string $xpath
     * @return array
     */
    protected function getXpathOptions($xpath)
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
     * Get XML path by name
     *
     * @param string $name
     * @return string
     * @throws \PreCommit\Composer\Exception
     */
    protected function getXpath($name)
    {
        if (!$name) {
            throw new Exception('Empty config name.');
        }
        switch ($name) {
            case 'password':
            case 'username':
            case 'project':
            case 'url':
                $name = 'tracker/' . $this->getTrackerType() . '/' . $name;
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
                $name = 'validators/FileFilter/filter/skip/paths/path';
                break;

            case 'exclude-file':
            case 'skip-file':
                $name = 'validators/FileFilter/filter/skip/files/file';
                break;

            case 'protect-path':
                $name = 'validators/FileFilter/filter/protect/paths/path';
                break;

            case 'protect-file':
                $name = 'validators/FileFilter/filter/protect/files/file';
                break;

            case 'allow-path':
                $name = 'validators/FileFilter/filter/allow/paths/path';
                break;

            case 'allow-file':
                $name = 'validators/FileFilter/filter/allow/files/file';
                break;

            case 'task':
                $name = 'tracker/' . $this->getTrackerType() . '/active_task';
                break;

            default:
                throw new Exception("Unknown config name '$name'.");
        }
        return $name;
    }

    /**
     * Get issues tracker type
     *
     * @return string
     */
    protected function getTrackerType()
    {
        if ($this->_trackerType) {
            return $this->_trackerType;
        }
        $this->_trackerType = $this->getConfig()->getNode(self::XPATH_TRACKER_TYPE);
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
     * @param Question|null                                     $question
     * @return array
     */
    protected function getScope(
        InputInterface $input, OutputInterface $output, $xpath, $question = null
    ) {
        $type = null;
        if (self::XPATH_TRACKER_TYPE !== $xpath) {
            $type = $this->getTrackerType();
        }
        $options = $this->scopeOptions;
        switch ($xpath) {
            case 'tracker/' . $type . '/active_task':
                return self::OPTION_SCOPE_PROJECT_SELF;
                break;
            case 'tracker/' . $type . '/project':
                return self::OPTION_SCOPE_PROJECT;
                break;

            case self::XPATH_TRACKER_TYPE:
            case 'tracker/' . $type . '/url':
                $default = 1;
                break;

            case 'tracker/' . $type . '/username':
            case 'tracker/' . $type . '/password':
                $default = 1;
                unset($options[2]);
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
            $question
                ?: $this->getSimpleQuestion()->getQuestion(
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
     * Get credentials scope
     *
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return array
     */
    protected function getCredentialsScope(InputInterface $input, OutputInterface $output)
    {
        $scopeOptions = $this->scopeOptions;
        unset($scopeOptions[1]);
        return $this->getScope(
            $input, $output,
            $this->getXpath('username'),
            $this->getSimpleQuestion()->getQuestion(
                "Set config scope credentials", 1,
                $scopeOptions
            )
        );
    }

    /**
     * Write config
     *
     * @param string $xpath
     * @param string $scope
     * @param string $value
     * @return $this
     * @throws Exception
     */
    protected function writeConfig($xpath, $scope, $value)
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
            $xpath = $this->isNameXpath($input) ? $name : $this->getXpath($name);
            $scope = $this->getScope($input, $output, $xpath);
            $this->writeConfig($xpath, $scope, $value);
        }
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
     * Write key-value option
     *
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return $this
     * @throws \PreCommit\Composer\Exception
     */
    protected function writeKeyValueOption(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getArgument('key')) {
            /**
             * Ignore if nothing to write
             */
            return $this;
        }

        $xpath = $this->isNameXpath($input)
            ? $input->getArgument('key')
            : $this->getXpath($input->getArgument('key'));

        $value = $this->getValue($input, $output, $xpath);
        $scope = $this->getScope($input, $output, $xpath);

        $this->writeConfig($xpath, $scope, $value);
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
        if (!$input->getArgument('value')) {
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
        return $input->getArgument('value');
    }

    /**
     * @inheritDoc
     */
    public function setApplication(Application $application = null)
    {
        parent::setApplication($application);
        $this->getHelperSet()->set(new Helper\Config());
        $this->getHelperSet()->set(new Helper\Config\Set());
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

        $help = <<<HELP
This command can set CommitHook configuration.
Allowed predefined keys:
Tracker:
    tracker
        Issue tracker type code (jira, github etc).
    url
        Issue tracker API URL.
    username
        Username for issue tracker authorization.
    password
        Password for issue tracker authorization.
    project
        Project key in selected issue tracker.
Files:
    exclude-extension, skip-ext
        Ignore validation for selected files with extension.
    exclude-path, skip-path
        Ignore validation for file by path.
    exclude-file, skip-file
        Ignore validation for file.
    protect-path
        Protect path from committing.
    protect-file
        Protect file from committing.
    allow-path
        Protect path from committing.
    allow-file
        Protect file from committing.
Issue:
    task
        Active task key. After setting issue key/No can be omitted.
HELP;

        $this->setHelp($help);
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
        $this->addArgument('key', InputArgument::OPTIONAL);
        $this->addArgument('value', InputArgument::OPTIONAL);

        /**
         * When this parameter set key must be an XML path
         */
        $this->addOption(
            'xpath', '-x', InputOption::VALUE_NONE,
            'XPath mode. "key" parameter will be considered as a full XML path.'
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
