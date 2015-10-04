<?php
namespace PreCommit\Composer\Command;

use PreCommit\Composer\Command\Helper\ProjectDir;
use PreCommit\Composer\Exception;
use PreCommit\Config;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * Base command abstract class
 *
 * @package PreCommit\Composer\Command
 */
abstract class CommandAbstract extends Command
{
    /**
     * Base commithook directory
     *
     * @var null|string
     */
    protected $commithookDir;

    /**
     * Construct
     *
     * @param string $commithookDir
     */
    public function __construct($commithookDir)
    {
        $this->commithookDir = $commithookDir;
        parent::__construct();
    }

    /**
     * Sets the application instance for this command.
     *
     * Set extra helper ProjectDir
     *
     * @param Application $application An Application instance
     * @throws \PreCommit\Composer\Exception
     * @api
     */
    public function setApplication(Application $application = null)
    {
        parent::setApplication($application);
        if (!$this->getHelperSet()) {
            throw new Exception('Helper set is not set.');
        }
        $this->getHelperSet()->set(new ProjectDir());
    }

    /**
     * Get config
     *
     * @return Config
     */
    public function getConfig()
    {
        return Config::getInstance(
            array('file' => $this->commithookDir
                            . DIRECTORY_SEPARATOR . 'LibHooks' . DIRECTORY_SEPARATOR . 'config.xml')
        );
    }

    /**
     * Configure command
     */
    protected function configure()
    {
        $this->configureCommand();
        $this->configureInput();
    }

    /**
     * Init command
     *
     * Set name, description, help
     *
     * @return CommandAbstract
     */
    abstract protected function configureCommand();

    /**
     * Init input definitions
     *
     * @return CommandAbstract
     */
    protected function configureInput()
    {
        $this->addOption(
            'project-dir', '-d', InputOption::VALUE_REQUIRED,
            'Path to project (VCS) root directory.'
        );
        return $this;
    }

    /**
     * Ask about GIT project root dir
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return array
     * @throws Exception
     */
    protected function askProjectDir(InputInterface $input, OutputInterface $output)
    {
        return $this->getProjectDirHelper()->getProjectDir($input, $output);
    }

    /**
     * Get project dir helper
     *
     * @return ProjectDir
     */
    protected function getProjectDirHelper()
    {
        return $this->getHelperSet()->get(ProjectDir::NAME);
    }

    /**
     * Get question helper
     *
     * @return QuestionHelper
     */
    protected function getQuestionHelper()
    {
        return $this->getHelperSet()->get('question');
    }

    /**
     * Get the question object with a formatted question
     *
     * @param string $question
     * @param string|int|null   $default
     * @param array  $options
     * @return \Symfony\Component\Console\Question\Question
     */
    protected function getQuestion($question, $default = null, array $options = array())
    {
        if ($options) {
            $question .= ' (' . implode('/', $options) . ')';
        }
        if ($default) {
            $question .= ' [' . $default . ']';
        }
        $question .= ': ';
        return new Question($question, $default);
    }

    /**
     * Get the question object with a formatted question
     *
     * @param string $question
     * @param string|int|null   $default
     * @param array  $options
     * @return \Symfony\Component\Console\Question\Question
     */
    protected function getQuestionConfirm($question, $default = 'y', array $options = array('y', 'n'))
    {
        return $this->getQuestion($question, $default, $options);
    }

    /**
     * Is output very verbose
     *
     * @param OutputInterface $output
     * @return bool
     */
    protected function isVeryVerbose(OutputInterface $output)
    {
        return $output->getVerbosity() >= OutputInterface::VERBOSITY_VERY_VERBOSE;
    }

    /**
     * Is output verbose
     *
     * @param OutputInterface $output
     * @return bool
     */
    protected function isVerbose(OutputInterface $output)
    {
        return $output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE;
    }
}
