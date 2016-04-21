<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
namespace PreCommit\Command\Command\Config;

use AndKirby\Crypter\Crypter;
use PreCommit\Command\Command\AbstractCommand;
use PreCommit\Command\Command\Helper;
use PreCommit\Command\Exception;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * CommitHooks command tester
 *
 * It will test all modified files
 *
 * @package PreCommit\Command
 */
class Set extends AbstractCommand
{
    /**#@+
     * Option scopes
     *
     * project-self: ~/.commithook/projects/PROJECT_NAME/commithook.xml
     * project:      PROJECT_DIR/commithook.xml
     * global:       ~/.commithook/commithook.xml
     */
    const OPTION_SCOPE_GLOBAL       = 'global';
    const OPTION_SCOPE_PROJECT      = 'project';
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
     * @var string
     */
    protected $trackerType;

    /**
     * Update status
     *
     * It will true if some file updated
     *
     * @var bool
     */
    protected $updated = false;

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
            if ($this->input->getArgument('key') === 'wizard') {
                $this->connectionWizard();

                if ($this->updated) {
                    $this->output->writeln('Configuration updated.');
                    $this->output->writeln('Do not forget to share project commithook.xml file with your team.');
                    $this->output->writeln('Enjoy!');
                }
            } else {
                $this->writePredefinedOptions();
                $this->writeKeyValueOption();

                if ($this->updated) {
                    $this->output->writeln(
                        'Configuration updated.'
                    );
                }
            }
        } catch (Exception $e) {
            if ($this->isDebug()) {
                throw $e;
            } else {
                $this->output->writeln($e->getMessage());

                return 1;
            }
        }

        return 0;
    }

    /**
     * {@inheritdoc}
     */
    public function setApplication(Application $application = null)
    {
        parent::setApplication($application);
        $this->getHelperSet()->set(new Helper\Config());
        $this->getHelperSet()->set(new Helper\Config\Set());
        $this->getHelperSet()->set(new Helper\Config\Writer());
        $this->getHelperSet()->set(new Helper\ClearCache());
    }

    /**
     * Connection tracker wizard
     *
     * @return $this
     */
    protected function connectionWizard()
    {
        $this->output->writeln('Set up issue tracker connection.');

        //type
        $options           = $this->getXpathOptions(self::XPATH_TRACKER_TYPE);
        $this->trackerType = $this->io->askQuestion(
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
        $url = $this->io->askQuestion(
            $this->getSimpleQuestion()->getQuestion(
                "'{$this->trackerType}' URL",
                $this->getConfig()->getNode($this->getXpath('url'))
            )
        );

        //username
        $username = $this->io->askQuestion(
            $this->getSimpleQuestion()->getQuestion(
                "'{$this->trackerType}' username",
                $this->getConfig()->getNode($this->getXpath('username'))
            )
        );

        //password
        $question = $this->getSimpleQuestion()->getQuestion(
            "'{$this->trackerType}' password",
            $this->getConfig()->getNode($this->getXpath('password'))
                ? '*****' : null
        );
        $question->setHiddenFallback(false);
        $question->setHiddenFallback(true);
        $password = $this->io->askQuestion($question);
        $password = '*****' !== $password ? $password : null;

        //project key
        $prjKey = $this->io->askQuestion(
            $this->getSimpleQuestion()->getQuestion(
                "Current '{$this->trackerType}' project key",
                $this->getConfig()->getNode($this->getXpath('project'))
            )
        );

        $scope = $this->getScope(self::XPATH_TRACKER_TYPE);

        $scopeCredentials = self::OPTION_SCOPE_PROJECT == $scope
            ? $this->getCredentialsScope() : $scope;

        $this->writeConfig(self::XPATH_TRACKER_TYPE, $scope, $this->trackerType);
        $this->writeConfig($this->getXpath('url'), $scope, $url);
        $this->writeConfig($this->getXpath('username'), $scopeCredentials, $username);
        if (null !== $password) {
            $this->writeConfig($this->getXpath('password'), $scopeCredentials, $password);
        }
        $this->writeConfig($this->getXpath('project'), self::OPTION_SCOPE_PROJECT, $prjKey);

        return $this;
    }

    /**
     * Write predefined options
     *
     * @param bool            $readAll
     * @throws Exception
     */
    protected function writePredefinedOptions($readAll = false)
    {
        foreach ($this->defaultOptions as $name) {
            $value = $this->input->getOption($name);
            if (!$readAll && null === $value) {
                continue;
            }
            $xpath = $this->isNameXpath() ? $name : $this->getXpath($name);
            $scope = $this->getScope($xpath);
            $this->writeConfig($xpath, $scope, $value);
        }
    }

    /**
     * Write key-value option
     *
     * @return $this
     * @throws Exception
     */
    protected function writeKeyValueOption()
    {
        if (!$this->input->getArgument('key')) {
            /**
             * Ignore if nothing to write
             */
            return $this;
        }

        $xpath = $this->isNameXpath()
            ? $this->input->getArgument('key')
            : $this->getXpath($this->input->getArgument('key'));

        $value = $this->getValue($xpath);
        $scope = $this->getScope($xpath);

        $this->writeConfig($xpath, $scope, $value);

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
     * @throws Exception
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
                $name = 'tracker/'.$this->getTrackerType().'/'.$name;
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
                $name = 'tracker/'.$this->getTrackerType().'/active_task';
                break;

            default:
                throw new Exception("Unknown config name '$name'.");
        }

        return $name;
    }

    /**
     * Get XML path input options
     *
     * @param string          $xpath
     * @param Question|null   $question
     * @return array
     */
    protected function getScope($xpath, $question = null)
    {
        $type = null;
        if (self::XPATH_TRACKER_TYPE !== $xpath) {
            $type = $this->getTrackerType();
        }
        $options = $this->scopeOptions;
        switch ($xpath) {
            case 'tracker/'.$type.'/active_task':
                return self::OPTION_SCOPE_PROJECT_SELF;
                break;
            case 'tracker/'.$type.'/project':
                return self::OPTION_SCOPE_PROJECT;
                break;

            case self::XPATH_TRACKER_TYPE:
            case 'tracker/'.$type.'/url':
                $default = 1;
                break;

            case 'tracker/'.$type.'/username':
            case 'tracker/'.$type.'/password':
                $default = 1;
                unset($options[2]);
                break;

            default:
                $default = 3;
                break;
        }

        $scope = $this->getScopeOption();
        if ($scope && in_array($scope, $options)) {
            return $scope;
        }

        return $this->io->askQuestion(
            $question
                ?: $this->getSimpleQuestion()
                ->getQuestion("Set config scope ($xpath)", $default, $options)
        );
    }

    /**
     * Get credentials scope
     *
     * @return array
     */
    protected function getCredentialsScope()
    {
        $scopeOptions = $this->scopeOptions;
        unset($scopeOptions[1]);

        return $this->getScope(
            $this->getXpath('username'),
            $this->getSimpleQuestion()->getQuestion(
                "Set config scope credentials",
                1,
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
        //encrypt password TODO refactor this block
        if ('password' === $xpath
            || strpos($xpath, '/password')
        ) {
            $value = $this->encrypt($value);
        }

        $result = $this->getConfigHelper()->writeValue(
            $this->getConfigFile($scope),
            $xpath,
            $value
        );
        if (self::XPATH_TRACKER_TYPE === $xpath) {
            $this->trackerType = $value;
        }

        $this->updated = $result ?: $this->updated;

        return $this;
    }

    /**
     * Check if name option is XML path
     *
     * @return bool
     */
    protected function isNameXpath()
    {
        return (bool) $this->input->getOption('xpath');
    }

    /**
     * Get value
     *
     * @param string          $xpath
     * @return string
     */
    protected function getValue($xpath)
    {
        if (!$this->input->getArgument('value')) {
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

            return $this->io->askQuestion($question);
        }

        return $this->input->getArgument('value');
    }

    /**
     * Get issues tracker type
     *
     * @return string
     */
    protected function getTrackerType()
    {
        if ($this->trackerType) {
            return $this->trackerType;
        }
        $this->trackerType = $this->getConfig()->getNode(self::XPATH_TRACKER_TYPE);
        if (!$this->trackerType) {
            new Exception('Tracker type is not set. Please use command: commithook config --tracker [TRACKER]');
        }

        return $this->trackerType;
    }

    /**
     * Get scope option
     *
     * @return null|string
     */
    protected function getScopeOption()
    {
        if ($this->input->getOption(self::OPTION_SCOPE_GLOBAL)) {
            return self::OPTION_SCOPE_GLOBAL;
        }
        if ($this->input->getOption(self::OPTION_SCOPE_PROJECT)) {
            return self::OPTION_SCOPE_PROJECT;
        }
        if ($this->input->getOption(self::OPTION_SCOPE_PROJECT_SELF)) {
            return self::OPTION_SCOPE_PROJECT_SELF;
        }

        return null;
    }

    /**
     * Encrypt password
     *
     * @param string $password
     * @return string
     */
    protected function encrypt($password)
    {
        $crypter = new Crypter();

        return $crypter->encrypt($password);
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
     * @param string $scope
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
     * Init default helpers
     *
     * @return $this
     */
    protected function configureCommand()
    {
        $this->setName('config');

        //@startSkipCommitHooks
        $help
            = <<<HELP
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
        //@finishSkipCommitHooks

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
            'xpath',
            '-x',
            InputOption::VALUE_NONE,
            'XPath mode. "key" parameter will be considered as a full XML path.'
        );

        /**
         * Scope options
         */
        $this->addOption(
            'global',
            '-g',
            InputOption::VALUE_NONE,
            'Save config in global configuration file.'
        );
        $this->addOption(
            'project-self',
            '-s',
            InputOption::VALUE_NONE,
            'Save config in project private(!) configuration file. PROJECT_DIR/commithook-self.xml'
        );
        $this->addOption(
            'project',
            '-P',
            InputOption::VALUE_NONE,
            'Save config in project configuration file. PROJECT_DIR/commithook.xml'
        );

        foreach ($this->defaultOptions as $name) {
            $this->addOption(
                $name,
                null,
                InputOption::VALUE_OPTIONAL,
                "Tracker connection '$name' option."
            );
        }

        return $this;
    }
}
