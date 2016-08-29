<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
namespace PreCommit\Console\Command\Config;

use PreCommit\Config;
use PreCommit\Console\Command\AbstractCommand;
use PreCommit\Console\Exception;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Question\Question;

/**
 * This command can define path for skipping validation
 *
 * @package PreCommit\Console\Command\Config\File
 */
abstract class AbstractConfiguredCommand extends Set
{
    /**
     * Key argument
     *
     * @var string
     */
    protected $key;

    /**
     * Value argument
     *
     * @var string
     */
    protected $value;

    /**
     * Get Xpath to console command configuration
     *
     * @return string
     */
    public function getConfigXpath()
    {
        return 'console/'
               .str_replace(':', '/', $this->getDefinedName());
    }

    /**
     * Process pair key-value
     *
     * @return int
     * @throws Exception
     */
    protected function processValue()
    {
        if (!$this->getKey()) {
            //show help if key is not defined
            $this->io->writeln("This command may set up configuration.");
            $this->io->writeln("Please use \"<comment>commithook {$this->getDefinedName()} --help</comment>\" to get more information.");

            return 0;
        }

        $this->validateConfigKey();

        if (null === $this->getValue() && $this->getKey() && !$this->shouldUnset()) {
            /**
             * Reading mode
             */
            $value = $this->getShowValue($this->getKeyXpath());
            if ($value !== trim($value)) {
                $value = "\"$value\"";
            }
            $this->io->writeln($value);
        } elseif ($this->getKey()) {
            /**
             * Writing mode
             */
            $value = $this->fetchValue($this->getKeyXpath());
            $scope = $this->getScope($this->getKeyXpath());

            $this->writeConfig($this->getKeyXpath(), $scope, $value);

            if ($this->updated) {
                if ($this->isVerbose()) {
                    $this->output->writeln(
                        'Configuration updated.'
                    );
                }
            } else {
                if ($this->isVerbose()) {
                    $this->output->writeln(
                        'Configuration already defined.'
                    );
                }

                return self::SHELL_CODE_CONF_DEFINED;
            }
        } else {
            $this->io->writeln($this->getProcessedHelp());
        }

        return 0;
    }

    /**
     * Get value by xpath
     *
     * @param string $xpath
     * @return null|string
     */
    protected function getXpathValue($xpath)
    {
        return $this->getConfig()->getNode($xpath);
    }

    /**
     * Get XML path input options
     *
     * @param string        $xpath
     * @param Question|null $question
     * @return int
     */
    protected function getScope($xpath, $question = null)
    {
        $default = $this->getConfigDefaultScope();

        if ($default) {
            return $default;
        }

        $scope   = $this->getScopeOption();
        $options = $this->getConfigScopeOptions();

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
     * Get available scope options
     *
     * All scopes are available by default if no configuration.
     * If added one scope rest ones will be excluded.
     *
     * @return array
     */
    protected function getConfigScopeOptions()
    {
        $optionsConfig = $this->getKeyConfig()->getNodeArray('scope', false);

        $hasConfig = false;
        foreach ($this->scopeOptions as $scopeOption) {
            if (isset($optionsConfig[$scopeOption])) {
                $hasConfig = true;
            }
        }

        if (!$hasConfig) {
            return $this->scopeOptions;
        }

        $options = [];
        foreach ($this->scopeOptions as $key => $scopeOption) {
            if (isset($optionsConfig[$scopeOption]) && (int) $optionsConfig[$scopeOption]) {
                $options[$key] = $scopeOption;
            }
        }

        return $options;
    }

    /**
     * Get xpath of key
     *
     * @return string
     */
    protected function getKeyXpath()
    {
        return $this->getKeyConfig()->getNode('xpath', false);
    }

    /**
     * Get default scope ID
     *
     * @return int
     */
    protected function getConfigDefaultScope()
    {
        $default = $this->getKeyConfig()->getNode('scope/default', false);

        return $default && in_array($default, $this->scopeOptions) ? $default : null;
    }

    /**
     * Get key name
     *
     * @return string
     */
    protected function getKey()
    {
        if (null === $this->key) {
            $this->key = $this->input->getArgument('key');
        }

        return $this->key;
    }

    /**
     * Get key name
     *
     * @return string
     */
    protected function getValue()
    {
        if (null === $this->value) {
            $this->value = $this->input->getArgument('value');
        }

        return $this->value;
    }

    /**
     * Init default helpers
     *
     * @return $this
     */
    protected function configureCommand()
    {
        $this->setName($this->getDefinedName());

        $this->setDescription(
            $this->getConfigDescription()
        );
        $this->setHelp(
            $this->getDefinedHelp()
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
        AbstractCommand::configureInput();

        $this->setUnsetOption();
        $this->setScopeOptions();

        $this->addArgument('key', InputArgument::OPTIONAL, 'Configuration key.');
        $this->addArgument('value', InputArgument::OPTIONAL, 'Configuration value.');

        return $this;
    }

    /**
     * Get command name
     *
     * @return string
     */
    abstract protected function getDefinedName();

    /**
     * Get defined help
     *
     * @return string
     */
    protected function getDefinedHelp()
    {
        $list = [];
        foreach ($this->getConfigArguments() as $name => $node) {
            $list[$name] = $node->getNode('description', false);
        }

        return ($this->getConfigDescription() ? $this->getConfigDescription().PHP_EOL : '')
               .'Allowed configuration keys:'.PHP_EOL
               .$this->formatList($list);
    }

    /**
     * Get config key arguments list
     *
     * @return Config[]
     */
    protected function getConfigArguments()
    {
        static $nodes;

        if ($nodes) {
            return $nodes;
        }

        $nodes = $this->getConfigBase()->xpath(
            $this->getConfigXpath().'/args'
        );

        $nodes = $nodes ? $nodes[0]->children() : [];

        return $nodes;
    }

    /**
     * Get description from configuration
     *
     * @return string
     */
    protected function getConfigDescription()
    {
        return $this->getConfigBase()->getNode(
            $this->getConfigXpath().'/description'
        );
    }

    /**
     * Get key config node
     *
     * @return Config
     */
    protected function getKeyConfig()
    {
        return $this->getConfigArguments()
            ? $this->getConfigArguments()->{$this->getKey()} : null;
    }

    /**
     * Format list
     *
     * @param array $list
     * @param int   $minIndent
     * @return string
     */
    protected function formatList($list, $minIndent = 1)
    {
        $maxWidth = 0;
        $result   = '';
        array_walk(
            $list,
            function ($value, $key) use (&$maxWidth) {
                $maxWidth = max(strlen($key), $maxWidth);
            }
        );

        foreach ($list as $command => $help) {
            $result .= sprintf(
                '  <info>%s</info>%s%s'.PHP_EOL,
                $command,
                str_repeat(' ', $maxWidth - strlen($command) + $minIndent),
                $help
            );
        }

        return $result;
    }

    /**
     * Check existing
     *
     * @throws Exception
     */
    protected function validateConfigKey()
    {
        if (!$this->getKeyConfig()) {
            throw new Exception('Unknown config key "'.$this->getKey().'".');
        }
    }
}
