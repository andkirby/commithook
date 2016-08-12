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
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This command can define path to prohibit committing changes
 *
 * @package PreCommit\Console\Command\Config
 */
class Protect extends Set
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

            $path = $this->askProjectDir().'/'.$this->getValue();
            if (is_dir($path)) {
                $this->key = 'protect-path';
            } elseif (is_file($path)) {
                $this->key = 'protect-file';
            } else {
                throw new Exception("Unknown path '{$this->getValue()}'.");
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
        $this->setName('config:protect');

        $help = 'This command can define path to prohibit committing changes.';

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

    /**
     * Show set values
     *
     * @return $this
     */
    protected function showSetValues()
    {
        $this->key = 'protect-path';
        $this->processValue();

        $this->key = 'protect-file';
        $this->processValue();

        return $this;
    }
}
