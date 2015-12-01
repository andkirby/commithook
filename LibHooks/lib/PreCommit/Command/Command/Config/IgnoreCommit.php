<?php
namespace PreCommit\Command\Command\Config;

use PreCommit\Command\Command\CommandAbstract;
use PreCommit\Command\Command\Helper;
use PreCommit\Command\Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

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
    const XPATH_IGNORED_VALIDATORS = "hooks/pre-commit/ignore/validator/%s/*[text() = '1' or text() = 'true']";

    /**
     * XML path to status of ignoring of next commit
     */
    const XPATH_IGNORE_CODE = 'hooks/pre-commit/ignore/disable/code';

    /**
     * XML path to status of ignoring FileFilter for the next commit
     */
    const XPATH_IGNORE_PROTECTION = 'hooks/pre-commit/ignore/disable/protection';

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
            if ($input->getOption('show')) {
                $output->writeln(
                    $this->_getValidators($input)
                );
            } else {
                $this->checkOptions($input);
                $this->disableCodeValidation($input, $input->getOption('disable'));
                $this->disableProtection($input, $input->getOption('disable'));

                if ($this->isVerbose($output)) {
                    if ($this->_updated) {
                        $output->writeln(
                            'Validation will be ignored for the next commit.'
                        );
                    } else {
                        $output->writeln(
                            'You already defined this before.'
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
     * Get validators list which will be ignored
     *
     * @param InputInterface $input
     * @return array|null|string
     */
    protected function _getValidators(InputInterface $input)
    {
        $xpath = self::XPATH_IGNORED_VALIDATORS;
        if ($input->getOption('code')) {
            $xpath = sprintf($xpath, 'code');
        } elseif ($input->getOption('protection')) {
            $xpath = sprintf($xpath, 'protection');
        } else {
            $xpath = sprintf($xpath, '*');
        }
        return array_keys(
            $this->getConfig()->getNodesExpr($xpath)
        );
    }

    /**
     * Set TRUE to all if none set
     *
     * @param InputInterface $input
     * @return $this
     */
    protected function checkOptions(InputInterface $input)
    {
        if (!$input->getOption('code') && !$input->getOption('protection')) {
            $input->setOption('code', true);
            $input->setOption('protection', true);
        }
        return $this;
    }

    /**
     * Set config for ignoring code validation
     *
     * @param InputInterface $input
     * @param bool           $remove
     * @return $this
     */
    protected function disableCodeValidation(InputInterface $input, $remove = false)
    {
        if ($input->getOption('code')) {
            $this->writeConfig(
                self::XPATH_IGNORE_CODE,
                static::OPTION_SCOPE_PROJECT_SELF, !$remove
            );
        }
        return $this;
    }

    /**
     * Set config for ignoring file protection
     *
     * @param InputInterface $input
     * @param bool           $remove
     * @return $this
     */
    protected function disableProtection(InputInterface $input, $remove = false)
    {
        if ($input->getOption('protection')) {
            $this->writeConfig(
                self::XPATH_IGNORE_PROTECTION,
                static::OPTION_SCOPE_PROJECT_SELF, !$remove
            );
        }
        return $this;
    }

    /**
     * Init default helpers
     *
     * @return $this
     */
    protected function configureCommand()
    {
        $this->setName('blind-commit');

        $help
            = <<<HELP
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
            'show', 's', InputOption::VALUE_NONE,
            'Show validator names which will be omitted.'
        );
        $this->addOption(
            'code', 'c', InputOption::VALUE_NONE,
            'Ignore code validation.'
        );
        $this->addOption(
            'protection', 't', InputOption::VALUE_NONE,
            'Ignore file protection.'
        );
        $this->addOption(
            'disable', 'r', InputOption::VALUE_NONE,
            'Disable ignoring of the next commit.'
        );
        return $this;
    }
}
