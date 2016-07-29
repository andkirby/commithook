<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
namespace PreCommit\Command\Command;

use PreCommit\Command\Command\Helper\ProjectDir;
use PreCommit\Command\Command\Helper\SimpleQuestion;
use PreCommit\Command\Exception;
use PreCommit\Config;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Base command abstract class
 *
 * @package PreCommit\Command\Command
 */
abstract class AbstractCommand extends Command
{
    /**
     * Base commithook directory
     *
     * @var null|string
     */
    protected $commithookDir;

    /**
     * Output
     *
     * @var OutputInterface
     */
    protected $output;

    /**
     * Input
     *
     * @var InputInterface
     */
    protected $input;

    /**
     * Input/Output model
     *
     * @var SymfonyStyle
     */
    protected $io;

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
     * @throws \PreCommit\Command\Exception
     * @api
     */
    public function setApplication(Application $application = null)
    {
        parent::setApplication($application);
        if (!$this->getHelperSet()) {
            throw new Exception('Helper set is not set.');
        }
        $this->getHelperSet()->set(new ProjectDir());
        $this->getHelperSet()->set(new SimpleQuestion());
    }

    /**
     * Get config
     *
     * @return Config
     */
    public function getConfig()
    {
        static $config;
        if (null === $config) {
            //TODO Make single load
            $config = Config::initInstance(
                array(
                    'file' => $this->commithookDir.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'root.xml',
                )
            );
            Config::setProjectDir($this->askProjectDir($this->input, $this->output));
            if (!Config::loadCache()) {
                Config::mergeExtraConfig();
            }
            $config = Config::getInstance();
        }

        return $config;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input  = $input;
        $this->output = $output;
        $this->io     = new SymfonyStyle($input, $output);
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
     * @return AbstractCommand
     */
    abstract protected function configureCommand();

    /**
     * Init input definitions
     *
     * @return AbstractCommand
     */
    protected function configureInput()
    {
        $this->addOption(
            'project-dir',
            '-d',
            InputOption::VALUE_REQUIRED,
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
        static $dir;
        if (!$dir) {
            $option = $input->getOption('project-dir');
            $dir    = $this->getProjectDirHelper()->getProjectDir($input, $output, $option);
        }

        return $dir;
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
     * Get question helper
     *
     * @return SimpleQuestion
     */
    protected function getSimpleQuestion()
    {
        return $this->getHelperSet()->get('simple_question');
    }

    /**
     * Is output very verbose
     *
     * @return bool
     */
    protected function isVeryVerbose()
    {
        return $this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERY_VERBOSE;
    }

    /**
     * Is output verbose
     *
     * @return bool
     */
    protected function isVerbose()
    {
        return $this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE;
    }

    /**
     * Is output verbose
     *
     * @return bool
     */
    protected function isDebug()
    {
        return $this->output->getVerbosity() >= OutputInterface::VERBOSITY_DEBUG;
    }
}
