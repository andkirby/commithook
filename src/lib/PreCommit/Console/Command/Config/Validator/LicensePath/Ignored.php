<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
namespace PreCommit\Console\Command\Config\Validator\LicensePath;

use PreCommit\Console\Command\AbstractCommand;
use PreCommit\Console\Command\Config\Set;
use PreCommit\Console\Exception;
use PreCommit\Helper\License;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This command can define license ignored path
 *
 * @package PreCommit\Console\Command\Config
 */
class Ignored extends Set
{
    /**
     * Key name for processing
     *
     * @var string
     */
    protected $key;

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
        AbstractCommand::execute($input, $output);

        if ($this->getValue()) {
            $this->normalizeValue();

            return $this->processValue();
        } elseif ($this->shouldUnset()) {
            return $this->processValue();
        } else {
            $this->showSetValues();

            return 0;
        }
    }

    /**
     * Get XPath
     *
     * @param string $name
     * @return string
     */
    protected function getXpath($name)
    {
        $helper = new License();
        if ($this->shouldWriteValue()) {
            return $helper->getPathsXpath($this->getKey()).'/'
                   .$this->getHelperSet()
                       ->get('commithook_config_file')->path2XmlNode($this->getValue());
        }

        return $helper->getPathsXpath($this->getKey());
    }

    /**
     * Filter value
     *
     * @return string
     * @throws Exception
     */
    protected function normalizeValue()
    {
        $this->setValue(
            $this->getHelperSet()->get('commithook_config_file')
                ->normalizePath($this->getValue())
        );

        /**
         * Check empty value but "/" can be accepted
         */
        if (!$this->getValue()) {
            throw new Exception('No value defined.');
        }

        $path = $this->askProjectDir().'/'.ltrim($this->getValue(), '/');

        /**
         * Check exist path
         *
         * Ignore paths with masks
         */
        if (false === strpos($this->getValue(), '*') && !is_dir($path) && !is_file($path)) {
            throw new Exception("Unknown path '{$this->getValue()}'.");
        }

        return $this;
    }

    /**
     * Show set values
     *
     * @return $this
     */
    protected function showSetValues()
    {
        $this->processValue();

        return $this;
    }

    /**
     * Get key name
     *
     * @return string
     * @throws Exception
     */
    protected function getKey()
    {
        if (null === $this->key) {
            $this->key = 'ignored';
        }

        return $this->key;
    }

    /**
     * Init input definitions
     *
     * @return $this
     */
    protected function configureInput()
    {
        AbstractCommand::configureInput();

        $this->addArgument('value', InputArgument::OPTIONAL);

        $this->setScopeOptions();
        $this->setUnsetOption();

        return $this;
    }

    /**
     * Removed using scope.
     *
     * @return bool
     */
    protected function useDefaultScopeByDefault()
    {
        return true;
    }

    /**
     * Init default helpers
     *
     * @return $this
     */
    protected function configureCommand()
    {
        $this->setName('validator:license:path:ignore');

        $help = 'This command can define path for ignoring license block set.';

        $this->setHelp($help);
        $this->setDescription($help);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function hasXpathOption()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    protected function writePredefinedOptions($readAll = false)
    {
        return;
    }

    /**
     * Get default scope
     *
     * @param string $xpath
     * @param string $type
     * @return int
     */
    protected function getDefaultScope($xpath, $type)
    {
        return self::OPTION_SCOPE_PROJECT;
    }

    /**
     * Get value by xpath
     *
     * @param string $xpath
     * @return null|string
     */
    protected function getXpathValue($xpath)
    {
        return $this->getConfig()->getNodeArray($xpath);
    }

    /**
     * {@inheritdoc}
     */
    protected function showValue()
    {
        $value = (array) $this->getConfig()->getNodesExpr($this->getArgumentXpath().'/*');
        $value && $this->io->writeln(implode(PHP_EOL, $value));

        return $this;
    }
}
