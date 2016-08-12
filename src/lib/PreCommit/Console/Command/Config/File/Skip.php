<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
namespace PreCommit\Console\Command\Config\File;

use PreCommit\Console\Command\AbstractCommand;
use PreCommit\Console\Command\Config\Set;
use PreCommit\Console\Exception;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This command can define path for skipping validation
 *
 * @package PreCommit\Console\Command\Config\File
 */
class Skip extends Set
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
            return $this->processValue();
        } else {
            $this->showSetValues();

            return 0;
        }
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
            if (!$this->getValue()) {
                throw new Exception('No value defined.');
            }

            if ($this->isExtension()) {
                $this->key = 'skip-ext';
            } else {
                $path = $this->askProjectDir().'/'.$this->getValue();
                if (is_dir($path)) {
                    $this->key = 'skip-path';
                } elseif (is_file($path)) {
                    $this->key = 'skip-file';
                } else {
                    throw new Exception("Unknown path '{$this->getValue()}'.");
                }
            }
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
        $this->addOption('ext', null, InputOption::VALUE_NONE, 'Set skipping for extension.');

        $this->setScopeOptions();

        return $this;
    }

    /**
     * Init default helpers
     *
     * @return $this
     */
    protected function configureCommand()
    {
        $this->setName('config:skip');

        $help = 'Skip validation for a path.';

        $this->setHelp($help);
        $this->setDescription($help);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function isNameXpath()
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
     * Get unique option status
     *
     * @return string
     */
    protected function isExtension()
    {
        return $this->input->hasParameterOption('--ext');
    }

    /**
     * Show set values
     *
     * @return $this
     */
    protected function showSetValues()
    {
        if ($this->isExtension()) {
            $this->key = 'skip-ext';
            $this->processValue();
        } else {
            $this->key = 'skip-path';
            $this->processValue();

            $this->key = 'skip-file';
            $this->processValue();
        }

        return $this;
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
        return 2;
    }
}
