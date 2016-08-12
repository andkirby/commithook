<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
namespace PreCommit\Console\Command\Config\Tracker;

use PreCommit\Console\Command\AbstractCommand;
use PreCommit\Console\Command\Config\Set;
use PreCommit\Console\Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Wizard command for initializing connection to task tracker
 *
 * @package PreCommit\Console\Command\Config
 */
class Wizard extends Set
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

        $this->connectionWizard();

        //TODO Fix setting project name for GitHub

        if ($this->updated) {
            $this->output->writeln('Configuration updated.');
            $this->output->writeln('Do not forget to share project commithook.xml file with your team.');
            $this->output->writeln('Enjoy!');
        }

        return 0;
    }

    /**
     * {@inheritdoc}
     */
    protected function getScopeOption()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    protected function getKey()
    {
        return 'wizard';
    }

    /**
     * {@inheritdoc}
     */
    protected function getValue()
    {
        return null;
    }

    /**
     * Init input definitions
     *
     * @return $this
     */
    protected function configureInput()
    {
        AbstractCommand::configureInput();

        return $this;
    }

    /**
     * Init default helpers
     *
     * @return $this
     */
    protected function configureCommand()
    {
        $this->setName('tracker:wizard');

        $help = 'Wizard command for initializing connection to task tracker.';

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
}
