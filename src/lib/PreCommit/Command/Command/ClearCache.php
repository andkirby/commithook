<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
namespace PreCommit\Command\Command;

use PreCommit\Command\Command\Helper\ClearCache as ClearCacheHelper;
use PreCommit\Command\Exception;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * CommitHooks command tester
 *
 * It will test all modified files
 *
 * @package PreCommit\Command
 */
class ClearCache extends AbstractCommand
{
    /**
     * Init helpers
     *
     * @param Application $application An Application instance
     * @throws \PreCommit\Command\Exception
     * @api
     */
    public function setApplication(Application $application = null)
    {
        parent::setApplication($application);

        if (!$this->getHelperSet()) {
            throw new Exception('Helper set is not set.');
        }
        $this->getHelperSet()->set(new ClearCacheHelper());
    }

    /**
     * Execute command
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return int
     * @throws \Exception
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        try {
            //init config
            $this->getConfig();

            if (!$input->getOption('config') && !$input->getOption('issues')) {
                $this->getCacheClearHelper()->clear();
            } elseif ($input->getOption('config')) {
                $this->getCacheClearHelper()->clearConfigCache();
            } elseif ($input->getOption('issues')) {
                $this->getCacheClearHelper()->clearIssueCache();
            }
        } catch (Exception $e) {
            if ($this->isVeryVerbose($output)) {
                throw $e;
            } else {
                $output->writeln($e->getMessage());

                return 1;
            }
        }

        return 0;
    }

    /**
     * Init command
     *
     * @return $this
     */
    protected function configureCommand()
    {
        $this->setName('clear-cache');
        $this->setAliases(array('clear'));
        $this->setHelp(
            'This command can clear cache files. Clear all by default.'
        );
        $this->setDescription(
            'This command can clear cache files.'
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
        $this->addOption(
            'issues',
            'i',
            InputOption::VALUE_NONE,
            'Clear cache for issues/tasks requests (uses for commit messages).'
        );
        $this->addOption(
            'config',
            'c',
            InputOption::VALUE_NONE,
            'Clear cache of config files (all XML ones).'
        );
        parent::configureInput();

        return $this;
    }

    /**
     * Get helper for clearing cache
     *
     * @return ClearCacheHelper
     */
    protected function getCacheClearHelper()
    {
        return $this->getHelper(ClearCacheHelper::NAME);
    }
}
