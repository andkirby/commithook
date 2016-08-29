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
            $this->output->writeln('Do not forget to share project commithook files with your team.');
            $this->output->writeln('Enjoy!');
        }

        return 0;
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
        $options = $this->getXpathOptions(Set::XPATH_TRACKER_TYPE);
        $this->trackerType = $this->io->askQuestion(
            $this->getSimpleQuestion()->getQuestion(
                'Tracker type',
                array_search(
                    $this->getConfig()->getNode(Set::XPATH_TRACKER_TYPE),
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

        $scope = $this->getScope(Set::XPATH_TRACKER_TYPE);

        $scopeCredentials = Set::OPTION_SCOPE_PROJECT == $scope
            ? $this->getCredentialsScope() : $scope;

        $this->writeConfig(Set::XPATH_TRACKER_TYPE, $scope, $this->trackerType);
        $this->writeConfig($this->getXpath('url'), $scope, $url);
        $this->writeConfig($this->getXpath('username'), $scopeCredentials, $username);
        if (null !== $password) {
            $this->writeConfig($this->getXpath('password'), $scopeCredentials, $password);
        }
        $this->writeConfig($this->getXpath('project'), Set::OPTION_SCOPE_PROJECT, $prjKey);

        return $this;
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
