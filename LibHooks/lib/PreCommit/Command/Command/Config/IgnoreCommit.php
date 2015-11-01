<?php
namespace PreCommit\Command\Command\Config;

use PreCommit\Command\Command\CommandAbstract;
use PreCommit\Command\Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use PreCommit\Command\Command\Helper;

/**
 * CommitHooks command disable code validation for the next commit
 *
 * @package PreCommit\Command\Command\Config
 */
class IgnoreCommit extends Set
{
    /**
     * XML path to omitted validators on ignore next commit
     */
    const XPATH_IGNORED_VALIDATORS = "hooks/pre-commit/ignore/validators/*[text() = '1' or text() = 'true']";

    /**
     * XML path to status of ignoring next commit
     */
    const XPATH_IGNORE_NEXT_COMMIT = 'hooks/pre-commit/ignore/next_commit';

    /**
     * Issues tracker type
     *
     * @var
     */
    protected $_trackerType;

    /**
     * Update status
     *
     * It will true if some file updated
     *
     * @var bool
     */
    protected $_updated = false;

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
        CommandAbstract::execute($input, $output);

        try {
            if ($input->getOption('show-ignored-validators')) {
                $output->writeln(
                    $this->_getValidators()
                );
            } else {
                $this->writeConfig(
                    self::XPATH_IGNORE_NEXT_COMMIT,
                    static::OPTION_SCOPE_PROJECT_SELF, 1
                );

                if ($this->isVerbose($output)) {
                    if ($this->_updated) {
                        $output->writeln(
                            'Code validation will be ignored for the next commit.'
                        );
                    } else {
                        $output->writeln(
                            'You already defined this configuration before.'
                        );
                    }
                }
            }
        } catch (Exception $e) {
            if ($this->isDebug($output)) {
                throw $e;
            }
            $output->writeln($e->getMessage());
            return 1;
        }
        return 0;
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
        return static::OPTION_SCOPE_PROJECT_SELF;
    }

    /**
     * Init default helpers
     *
     * @return $this
     */
    protected function configureCommand()
    {
        $this->setName('blind-commit');

        $help = <<<HELP
Ignore code validation for the next commit.
HELP;

        $this->setHelp($help);
        $this->setDescription($help);
        return $this;
    }

    /**
     * Skip input definitions
     *
     * @return $this
     */
    protected function configureInput()
    {
        CommandAbstract::configureInput();
        $this->addOption(
            'show-ignored-validators', 's', InputOption::VALUE_NONE,
            'This option will show validator names which will not be used in code validation.'
        );
        return $this;
    }

    /**
     * Get validators list which will be ignored
     *
     * @return array|null|string
     */
    protected function _getValidators()
    {
        $list = $this->getConfig()->getNodesExpr(
            self::XPATH_IGNORED_VALIDATORS
        );
        $list = array_keys($list);
        return $list;
    }
}
