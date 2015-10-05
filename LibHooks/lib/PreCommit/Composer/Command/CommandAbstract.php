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
        static $config;
        if (null !== $config) {
            return $config;
        }

        //TODO Make single load
        $config = Config::getInstance(
            array('file' => $this->commithookDir
                            . DIRECTORY_SEPARATOR
                            . 'LibHooks' . DIRECTORY_SEPARATOR
                            . 'config' . DIRECTORY_SEPARATOR
                            . 'root.xml')
        );
        //set work directories
        Config::setSrcRootDir($this->commithookDir . '/LibHooks');
        if (!Config::loadCache()) {
            Config::mergeExtraConfig();
        }

        return $config;
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
     * @param string          $question
     * @param string|int|null $default
     * @param array           $options
     * @param int|null        $maxAttempts
     * @return \Symfony\Component\Console\Question\Question
     */
    protected function getQuestionConfirm(
        $question, $default = 'y', array $options = array('y', 'n'), $maxAttempts = null
    ) {
        return $this->getQuestion($question, $default, $options, $maxAttempts);
    }

    /**
     * Get the question object with a formatted question
     *
     * @param string          $question
     * @param string|int|null $default
     * @param array           $options
     * @param int             $maxAttempts
     * @return \Symfony\Component\Console\Question\Question
     */
    protected function getQuestion(
        $question, $default = null, array $options = array(), $maxAttempts = null
    ) {
        if (!$options || isset($options[0])) {
            /**
             * Simple options list mode
             */
            $isList = false;
            //format question
            $question .= '%s%s: ';
            $question = sprintf(
                $question,
                ($options ? ' (' . implode('/', $options) . ')' : ''),
                ($default ? ' [' . $default . ']' : '')
            );
        } else {
            /**
             * Options list mode
             */
            $isList = true;
            //format question
            $question .= '%s: ';
            $question = sprintf(
                $question,
                ($default ? ' [' . $default . ']' : '')
            );

            //options list
            $list = 'Options:' . "\n";
            foreach ($options as $key => $title) {
                $list .= " $key - $title" . ($default == $key ? ' (Recommended)' : '') . "\n";
            }
            $question = $list . $question;
        }

        $instance = new Question($question, $default);

        //set max attempts
        $instance->setMaxAttempts($maxAttempts ?: 3); //TODO move to constant

        //set validator
        if ($options) {
            $validator = function ($value) use ($options, $isList) {
                if ($isList && !array_key_exists($value, $options)
                    || !$isList && !in_array($value, $options, true)
                ) {
                    throw new Exception("Incorrect value '$value'.");
                }
                return $isList ? $options[$value] : $value;
            };
            $instance->setValidator($validator);
        }
        return $instance;
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
