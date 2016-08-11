<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
namespace PreCommit\Console\Command\Validator;

use PreCommit\Command\Exception;
use PreCommit\Console\Command\AbstractCommand;
use PreCommit\Console\Command\Config\Set;
use PreCommit\Console\Command\Helper\ValidatorHelper;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * CommitHooks command disable code validation for the next commit
 *
 * @package PreCommit\Console\Command\Config
 */
class ListAll extends Set
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

        $this->showList();

        return 0;
    }

    /**
     * {@inheritdoc}
     */
    public function setApplication(Application $application = null)
    {
        parent::setApplication($application);
        $this->getHelperSet()->set(new ValidatorHelper());
    }

    /**
     * Fetch validators list
     *
     * @return $this
     */
    protected function showList()
    {
        if ($this->isUnique()) {
            $this->output->writeln(
                implode(
                    PHP_EOL,
                    $this->getValidatorHelper()->fetchUniqueList(
                        $this->getConfig()
                    )
                )
            );
        } else {
            $this->showValidatorsFullTable();
        }

        return $this;
    }

    /**
     * Show validators table
     *
     * @return $this
     */
    protected function showValidatorsFullTable()
    {
        $rows = [];
        foreach ($this->getValidatorHelper()->fetchListByTypes($this->getConfig()) as $type => $list) {
            if (!$list) {
                continue;
            }
            foreach ($list as $validator => $status) {
                $rows[] = [$validator, $type, $status ? '+' : ''];
            }
        }

        // @codingStandardsIgnoreStart
        usort(
            $rows,
            function ($a, $b) {
                return $a[0] == $b[0] && $a[1] > $b[1] || $a[0] > $b[0];
            }
        );
        // @codingStandardsIgnoreEnd

        $table = new Table($this->output);
        $table
            ->setHeaders(['Name', 'Type', 'On',])
            ->setRows($rows);
        $table->render();

        return $this;
    }

    /**
     * Init default helpers
     *
     * @return $this
     */
    protected function configureCommand()
    {
        $this->setName('validator');
        $this->setAliases(['validator:list']);

        $help
            = <<<HELP
This command can show code validators list.
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

        $this->addOption(
            'unique',
            '-u',
            InputOption::VALUE_NONE,
            'Show validators by file types with status.'
        );

        return $this;
    }

    /**
     * Get unique option status
     *
     * @return string
     */
    protected function isUnique()
    {
        return $this->input->hasParameterOption('--unique') || $this->input->hasParameterOption('-u');
    }

    /**
     * Get code validator helper
     *
     * @return ValidatorHelper
     */
    protected function getValidatorHelper()
    {
        return $this->getHelperSet()->get('code_validator');
    }
}
