<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
namespace PreCommit\Console\Command\Config\File;

use PreCommit\Console\Command\AbstractCommand;
use PreCommit\Console\Exception;
use PreCommit\Validator\FileFilter;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This command can define path for allowing to commit a file
 *
 * @package PreCommit\Console\Command\Config\File
 */
class AllowDefault extends Protect
{
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

        return $this->processValue();
    }

    /**
     * Get key name
     *
     * @return string
     * @throws Exception
     */
    protected function getKey()
    {
        return 'allow_default';
    }

    /**
     * Init default helpers
     *
     * @return $this
     */
    protected function configureCommand()
    {
        $this->setName('files:allow-default');

        $help = 'This command can define configuration for allowing to commit files by default.';

        $this->setHelp($help);
        $this->setDescription($help);

        return $this;
    }

    /**
     * Get xpath
     *
     * @param string $name
     * @return string
     */
    protected function getXpath($name)
    {
        return FileFilter::XPATH_ALLOW_BY_DEFAULT;
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
     * Check if it should write value
     *
     * @return null|string
     */
    protected function shouldWriteValue()
    {
        return null !== $this->getValue();
    }

    /**
     * Filter value
     *
     * @param string $xpath
     * @return int
     */
    protected function fetchValue($xpath)
    {
        return (int) (bool) $this->getValue();
    }
}
