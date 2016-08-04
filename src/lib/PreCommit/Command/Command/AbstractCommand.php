<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
namespace PreCommit\Command\Command;

use PreCommit\Command\Command\Helper\ProjectDir;
use PreCommit\Command\Exception;
use PreCommit\Config;
use Rikby\Console\Command\AbstractCommand as ConsoleAbstractCommand;
use Rikby\Console\Helper\SimpleQuestionHelper;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Base command abstract class
 *
 * @package PreCommit\Command\Command
 */
abstract class AbstractCommand extends ConsoleAbstractCommand
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
     * @throws \PreCommit\Command\Exception
     * @api
     */
    public function setApplication(Application $application = null)
    {
        parent::setApplication($application);

        $this->getHelperSet()->set(new ProjectDir());
        $this->getHelperSet()->set(new SimpleQuestionHelper());
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
            $dir = $this->getProjectDirHelper()->getProjectDir($input, $output, $option);
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
}
