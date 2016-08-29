<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
namespace PreCommit\Console\Command\Config\Tracker;

use PreCommit\Console\Command\AbstractCommand;
use PreCommit\Console\Command\Config\Set;
use PreCommit\Console\Exception;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * CommitHooks command for setting "active task"
 *
 * @package PreCommit\Console\Command\Config
 */
class Task extends Set
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
     */
    protected function getKey()
    {
        return 'task';
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

        return $this;
    }

    /**
     * Init default helpers
     *
     * @return $this
     */
    protected function configureCommand()
    {
        $this->setName('tracker:task');

        $help = 'This command can set active task key. After setting issue key/number can be omitted.';

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
}
