<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
namespace PreCommit\Console\Command\Config\Tracker;

use PreCommit\Config;
use PreCommit\Console\Command\AbstractCommand;
use PreCommit\Console\Command\Config\Set;
use PreCommit\Console\Exception;
use PreCommit\Filter\ShortCommitMsg\Parser;
use PreCommit\Issue;
use PreCommit\Message;
use PreCommit\Processor\ErrorCollector;
use PreCommit\Validator\IssueStatus;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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

        $this->setFormatterStyle();

        return $this->processValue();
    }

    /**
     * Process value
     *
     * @return int
     * @throws Exception
     */
    protected function processValue()
    {
        $this->validateCommand();

        $issue = null;
        if ($this->canChangeTask()) {
            $issue = $this->loadIssue(
                $this->normalizeIssueKey($this->getValue())
            );
        }

        $status = parent::processValue();

        if ($issue && $this->canShowIssueInfo()) {
            //$valid = $this->isIssueStatusValid($issue);

            $this->output->writeln('Active issue has been updated.');
            $this->output->writeln(
                $this->getIssueOutputInfo($issue)
            );
        }

        if ($this->updated && $this->clearIssuesCache() !== 0) {
            $this->output->writeln(
                '<error>Cannot clear issues cache. Try to run command "commithook clear --issues".</error>'
            );
        }

        return $status;
    }

    /**
     * Check if should write value
     *
     * Added checking --info option, it should not if passed.
     *
     * @return bool
     */
    protected function shouldWriteValue()
    {
        return parent::shouldWriteValue() && !$this->hasInfoOption();
    }

    /**
     * Show full task information
     *
     * @return $this
     */
    protected function showValue()
    {
        if ($this->hasInfoOption()) {
            $key = $this->getValue() ?: $this->getXpathValue($this->getXpath($this->getKey()));
            $key = $this->normalizeIssueKey($key);
            if ($key) {
                $this->output->writeln($this->getIssueOutputInfo($this->loadIssue($key)));
            }
        } else {
            parent::showValue();
        }

        return $this;
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
        $this->addOption('info', 'i', InputOption::VALUE_NONE, 'Show detailed information about an issue.');

        return $this;
    }

    /**
     * Check if has --info option
     *
     * @return bool
     */
    protected function hasInfoOption()
    {
        return $this->input->hasParameterOption(['-i', '--info']);
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

    /**
     * Clear issues cache
     */
    protected function clearIssuesCache()
    {
        $clearCache = $this->getApplication()->find('clear-cache');
        $input      = new ArrayInput(['--issues']);

        return $clearCache->run($input, $this->output);
    }

    /**
     * Check if can show issue info
     *
     * @return bool
     */
    protected function canShowIssueInfo()
    {
        return $this->updated
               && $this->output->getVerbosity() >= OutputInterface::VERBOSITY_NORMAL;
    }

    /**
     * Load issue
     *
     * @param string $key
     * @return Issue\AdapterInterface
     */
    protected function loadIssue($key)
    {
        $issue = Issue::factory($key);
        $issue->getStatus();

        return $issue;
    }

    /**
     * Validate status
     *
     * @param Issue\AdapterInterface $issue
     * @return bool
     */
    protected function isIssueStatusValid(Issue\AdapterInterface $issue)
    {
        $statusValidator    = new IssueStatus(['errorCollector' => new ErrorCollector()]);
        $messageStub        = new Message();
        $messageStub->issue = $issue;

        return $statusValidator->validate($messageStub, null);
    }

    /**
     * Check if can change task
     *
     * @return bool
     */
    protected function canChangeTask()
    {
        $exist = $this->getXpathValue($this->getXpath($this->getKey()));

        return $this->getValue() && (!$exist || $exist && $exist !== $this->getValue());
    }

    /**
     * Get output issue info
     *
     * @param Issue\AdapterInterface $issue
     * @return string
     */
    protected function getIssueOutputInfo(Issue\AdapterInterface $issue)
    {
        $key   = $issue->getKey();
        $first = sprintf(
            'Issue %s %s (%s).',
            "<{$issue->getType()}>{$issue->getType()}</{$issue->getType()}>",
            "<info>{$key}</info>",
            "<comment>{$issue->getStatus()}</comment>"
        );

        return $first
               .PHP_EOL
               .'<comment>summary:</comment> '.$issue->getSummary()
               .PHP_EOL
               .'<comment>type:</comment>    '.$issue->getOriginalType();
    }

    /**
     * Set up formatter style
     *
     * @return $this
     */
    protected function setFormatterStyle()
    {
        $style = new OutputFormatterStyle('cyan');
        $this->output->getFormatter()->setStyle('task', $style);
        $style = new OutputFormatterStyle('red');
        $this->output->getFormatter()->setStyle('bug', $style);

        return $this;
    }

    /**
     * Check if tracker type is defined
     *
     * @throws Exception
     */
    protected function validateCommand()
    {
        if (!Config::getInstance()->getNode('tracker/type')) {
            throw new Exception(
                'Tracker type is not defined.'
                .PHP_EOL.'Please define it via command "commithook config --project tracker %value%"'
                .PHP_EOL.'Allowed values: jira, github.'
            );
        }
    }

    /**
     * Normalize issue key
     *
     * @param string $key
     * @return string
     */
    protected function normalizeIssueKey($key)
    {
        return Parser::factory(null)->normalizeIssueKey($key);
    }
}
