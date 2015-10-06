<?php
namespace PreCommit\Composer\Command\Config;

use PreCommit\Composer\Command\CommandAbstract;
use PreCommit\Composer\Exception;
use PreCommit\Config;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

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
     * Options levels
     *
     * project-self: ~/.commithook/projects/PROJECT_NAME/commithook.xml
     * project:      PROJECT_DIR/commithook.xml
     * global:       ~/.commithook/commithook.xml
     */
    const OPTION_LEVEL_PROJECT_SELF = 'project-self';
    const OPTION_LEVEL_GLOBAL = 'global';
    const OPTION_LEVEL_PROJECT = 'project';
    /**#@-*/

    /**
     * Tracker type XML path
     */
    const XPATH_TRACKER_TYPE = 'tracker/type';

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
        $xpath = $this->getXpath($input, $output);
        $value = $this->getValue(
            $input, $output,
            $xpath
        );
        $scope = $this->getScope($input, $output, $xpath);
        $this->writeConfig($input, $output, $xpath, $scope, $value);
        return 0;
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return string
     * @throws \PreCommit\Composer\Exception
     */
    protected function getXpath(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getOption('name');
        if (!$name) {
            throw new Exception('Please set name option.');
        }
        if ($this->isNameXpath($input)) {
            return $name;
        }
        switch ($name) {
            case 'password':
            case 'username':
            case 'project':
            case 'url':
                $name = $this->getTrackerType($input, $output) . '/' . $name;
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
        static $type;
        if ($type) {
            return $type;
        }
        $type = $this->getConfig()->getNode(self::XPATH_TRACKER_TYPE);
        if (!$type) {
            if ($ask) {
                $type  = $this->getQuestionHelper()->ask(
                    $input, $output,
                    $this->getSimpleQuestion()->getQuestion(
                        'Set issue tracker', null,
                        $this->getXpathOptions($input, $output, self::XPATH_TRACKER_TYPE)
                    )
                );
                $url   = $this->getQuestionHelper()->ask(
                    $input, $output,
                    $this->getSimpleQuestion()->getQuestion(
                        "Set tracker URL ($type)", null
                    )
                );
                $scope = $this->getScope($input, $output, self::XPATH_TRACKER_TYPE);
                $this->writeConfig($input, $output, self::XPATH_TRACKER_TYPE, $scope, $type);
                $this->writeConfig($input, $output, $type . '/url', $scope, $url);
            }
            if (!$type) {
                new Exception('Tracker type is not set. Please use command: commithook config --name type');
            }
        }
        return $type;
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
        switch ($xpath) {
            case '' . $type . '/project':
                return self::OPTION_LEVEL_PROJECT;
                break;

            case self::XPATH_TRACKER_TYPE:
                $default         = 2;
                $questionOptions = array(
                    1 => self::OPTION_LEVEL_GLOBAL,
                    2 => self::OPTION_LEVEL_PROJECT,
                );
                break;

            case '' . $type . '/url':
            case '' . $type . '/username':
            case '' . $type . '/password':
                return self::OPTION_LEVEL_GLOBAL;
                break;

            default:
                $default         = 3;
                $questionOptions = array(
                    1 => self::OPTION_LEVEL_GLOBAL,
                    2 => self::OPTION_LEVEL_PROJECT,
                    3 => self::OPTION_LEVEL_PROJECT_SELF,
                );
                break;
        }

        $scope = $this->getScopeOption($input, $output);
        if ($scope && in_array($scope, $questionOptions)) {
            return $scope;
        }

        return $this->getQuestionHelper()->ask(
            $input, $output,
            $this->getSimpleQuestion()->getQuestion(
                'Set config scope', $default,
                $questionOptions
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
        if ($input->getOption(self::OPTION_LEVEL_GLOBAL)) {
            return self::OPTION_LEVEL_GLOBAL;
        }
        if ($input->getOption(self::OPTION_LEVEL_PROJECT)) {
            return self::OPTION_LEVEL_PROJECT;
        }
        if ($input->getOption(self::OPTION_LEVEL_PROJECT_SELF)) {
            return self::OPTION_LEVEL_PROJECT_SELF;
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
     * @param string|null                                       $value
     * @throws Exception
     */
    protected function writeConfig(InputInterface $input, OutputInterface $output, $xpath, $scope, $value = null)
    {
        $file   = $this->getConfigFile($scope);
        $config = $this->loadConfig($file);
        if ($config->getNode($xpath) == $value) {
            throw new Exception("The same value '$value' already defined for XPath '$xpath'.");
        }
        Config::getXmlMerger()->merge(
            $config,
            $this->getXmlUpdate($xpath, $value)
        );
        $config->asXML($file);
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
        if (self::OPTION_LEVEL_GLOBAL == $scope) {
            return $this->getConfig()->getConfigFile('userprofile');
        } elseif (self::OPTION_LEVEL_PROJECT == $scope) {
            return $this->getConfig()->getConfigFile('project');
        } elseif (self::OPTION_LEVEL_PROJECT_SELF == $scope) {
            return $this->getConfig()->getConfigFile('project_local');
        } else {
            throw new \PreCommit\Exception("Unknown scope '$scope'.");
        }
    }

    /**
     * Load config
     *
     * @param string $file
     * @return Config
     * @throws \PreCommit\Exception
     */
    protected function loadConfig($file)
    {
        if (!file_exists($file)) {
            $xml
                = <<<XML
<?xml version="1.0"?>
<config>
</config>
XML;
            if (!file_put_contents($file, $xml)) {
                throw new Exception("Cannot write config file '$file'.");
            }
        }
        $config = Config::loadInstance(array('file' => $file), false);
        return $config;
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
     * @return CommandAbstract
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
        return $this;
    }

    /**
     * Get XML object with value
     *
     * @param string $xpath
     * @param string $value
     * @return \SimpleXMLElement
     */
    protected function getXmlUpdate($xpath, $value)
    {
        $nodes    = explode('/', $xpath);
        $startXml = '';
        $endXml   = '';
        $total    = count($nodes);
        foreach ($nodes as $level => $node) {
            $level += 1; //set real level because start from zero

            //sent indent
            $indent = str_repeat('    ', $level);
            $startXml .= $indent;
            $endXml .= $indent;

            if ($total === $level) {
                $startXml .= "<$node>$value</$node>\n";
            } else {
                $startXml .= "<$node>\n";
                $endXml .= "</$node>\n";
            }
        }
        $startXml = rtrim($startXml);
        $endXml   = rtrim($endXml);

        $xml
            = <<<XML
<?xml version="1.0"?>
<config>
{$startXml}
{$endXml}
</config>
XML;
        return simplexml_load_string($xml);
    }
}
