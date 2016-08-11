<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
namespace PreCommit\Console\Command\Validator;

use PreCommit\Console\Command\AbstractCommand;
use PreCommit\Console\Command\Config\Set;
use PreCommit\Console\Exception;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * CommitHooks command disable code validation for the next commit
 *
 * @package PreCommit\Console\Command\Config
 */
class Disable extends Set
{
    /**
     * XML path expression (mask) to enabled validators
     */
    const XPATH_ENABLED_VALIDATORS = "hooks/pre-commit/filetype/%s/validator/*[text() = '1' or text() = 'true']";
    /**
     * XML path expression (mask) to disabled validators
     */
    const XPATH_DISABLED_VALIDATORS = "hooks/pre-commit/filetype/%s/validators/*[text() = '0' or text() = 'false']";
    /**
     * XML path to validator
     */
    const XPATH_VALIDATOR = 'hooks/pre-commit/filetype/%s/validators/%s';
    /**
     * XML path to all file types
     */
    const XPATH_FILE_TYPES = 'hooks/pre-commit/filetype/*[validators/%s = \'%d\']';

    /**
     * File types list
     *
     * @var array
     */
    protected $fileTypes;

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

        $this->setValidatorStatus();

        if ($this->isVerbose()) {
            $this->showResultMessage();
        }

        return 0;
    }

    /**
     * Show result message
     *
     * @return $this
     */
    protected function showResultMessage()
    {
        $status = $this->getTargetStatus() ? 'enabled' : 'disabled';
        if ($this->updated) {
            $this->output->writeln(
                sprintf(
                    "Validator {$this->getTargetValidator()} {$status} for file type/s: %s.",
                    implode(', ', $this->getValidatorFileTypes())
                )
            );
        } else {
            $this->output->writeln(
                "Validator {$this->getTargetValidator()} already {$status}."
            );
        }

        return $this;
    }

    /**
     * Get file types where mentioned validator
     *
     * @return array
     */
    protected function getValidatorFileTypes()
    {
        if ($this->fileTypes === null) {
            $nodes = $this->getConfig()->getNodesExpr(
                sprintf(
                    self::XPATH_FILE_TYPES,
                    $this->getTargetValidator(),
                    (int) !$this->getTargetStatus()
                )
            );

            unset($nodes['before_all_original']);
            unset($nodes['before_all']);
            unset($nodes['after_all']);

            $this->fileTypes = array_keys($nodes);
        }

        return $this->fileTypes;
    }

    /**
     * Set validator status
     *
     * @return $this
     */
    protected function setValidatorStatus()
    {
        foreach ($this->getValidatorFileTypes() as $type) {
            $this->writeConfig(
                sprintf(self::XPATH_VALIDATOR, $type, $this->getTargetValidator()),
                static::OPTION_SCOPE_PROJECT,
                (int) $this->getTargetStatus()
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
        $this->setName('validator:disable');

        $help
            = <<<HELP
This command can disable validator.
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
        AbstractCommand::configureInput();
        $this->addArgument(
            'validator',
            InputArgument::REQUIRED,
            'Validator name.'
        );
        $this->addOption(
            'enable',
            null,
            InputOption::VALUE_NONE,
            'Enable validator back.'
        );

        return $this;
    }

    /**
     * Get target validator
     *
     * @return string
     */
    protected function getTargetValidator()
    {
        return $this->input->getArgument('validator');
    }

    /**
     * Get target status
     *
     * @return bool
     */
    protected function getTargetStatus()
    {
        return $this->input->hasParameterOption('--enable');
    }
}
