<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
namespace PreCommit\Console\Command;

use PreCommit\Config;
use PreCommit\Console\Exception;
use Rikby\Console\Command\AbstractCommand as ConsoleAbstractCommand;
use Rikby\Console\Helper\GitDirHelper;
use Rikby\Console\Helper\PhpBinHelper;
use Rikby\Console\Helper\SimpleQuestionHelper;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputOption;

/**
 * Base command abstract class
 *
 * @package PreCommit\Console\Command
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
     * @throws \PreCommit\Console\Exception
     * @api
     */
    public function setApplication(Application $application = null)
    {
        parent::setApplication($application);

        $this->getHelperSet()->set(new GitDirHelper());
        $this->getHelperSet()->set(new SimpleQuestionHelper());
        $this->getHelperSet()->set(new PhpBinHelper());
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
                [
                    'file' => $this->commithookDir.DIRECTORY_SEPARATOR.'src'.
                        DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'root.xml',
                ]
            );
            Config::setProjectDir($this->askProjectDir());
            if (!Config::loadCache()) {
                Config::mergeExtraConfig();
            }
            $config = Config::getInstance();
        }

        return $config;
    }

    /**
     * Get base config (without project files)
     *
     * @return Config
     */
    public function getConfigBase()
    {
        static $config;
        if (null === $config) {
            //TODO Make single load
            $config = Config::initInstance(
                [
                    'file' => $this->commithookDir.DIRECTORY_SEPARATOR.'src'
                        .DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'root.xml',
                ]
            );

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
     * @return array
     * @throws Exception
     */
    protected function askProjectDir()
    {
        static $dir;
        if (!$dir) {
            $dir = $this->getProjectDirHelper()->getGitDirectory(
                $this->input,
                $this->output,
                $this->input->getOption('project-dir')
            );
        }

        return $dir;
    }

    /**
     * Get project dir helper
     *
     * @return GitDirHelper
     */
    protected function getProjectDirHelper()
    {
        if ($this->getHelperSet()) {
            return $this->getHelperSet()->get(GitDirHelper::NAME);
        } else {
            return new GitDirHelper();
        }
    }
}
