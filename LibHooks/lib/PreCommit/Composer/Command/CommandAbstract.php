<?php
/**
 * Created by PhpStorm.
 * User: kirby
 * Date: 18.01.2015
 * Time: 17:49
 */

namespace PreCommit\Composer\Command;

use Composer\Command\Helper\DialogHelper;
use PreCommit\Composer\Exception;
use PreCommit\Config;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CommandAbstract
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
     * @return $this
     */
    abstract protected function configureCommand();

    /**
     * Init input definitions
     *
     * @return $this
     */
    protected function configureInput()
    {
        $this->addOption(
            'hook', null, InputOption::VALUE_REQUIRED,
            $this->getCustomHookOptionDescription()
        );
        foreach ($this->getAvailableHooks() as $hook) {
            $this->addOption(
                $hook, null, InputOption::VALUE_NONE,
                $this->getHookOptionDescription($hook)
            );
        }
        return $this;
    }

    /**
     * Get custom hook option description
     *
     * @return string
     */
    abstract protected function getCustomHookOptionDescription();

    /**
     * Get hook option description
     *
     * @param string $hook
     * @return string
     */
    abstract protected function getHookOptionDescription($hook);

    /**
     * Get available hooks in CommitHooks application
     *
     * @return array
     */
    protected function getAvailableHooks()
    {
        return array('commit-msg', 'pre-commit');
    }

    /**
     * Get target files
     *
     * @param InputInterface   $input
     * @param OutputInterface $output
     * @return array
     * @throws Exception
     */
    protected function getTargetFiles(InputInterface $input, OutputInterface $output)
    {
        if (!$this->isAskedSpecificFile($input)) {
            if ($this->isVeryVerbose($output)) {
                $output->writeln('All files mode.');
            }
            return $this->getAvailableHooks();
        }

        return $this->getOptionTargetFiles($input, $output);
    }

    /**
     * Get status of asked specific hook files to delete
     *
     * @param InputInterface $input
     * @return bool
     */
    protected function isAskedSpecificFile(InputInterface $input)
    {
        if ($input->getOption('hook')) {
            return true;
        }
        foreach ($this->getAvailableHooks() as $hook) {
            if ($input->getOption($hook)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get target files from input options
     *
     * @param InputInterface   $input
     * @param OutputInterface $output
     * @return array
     * @throws Exception
     */
    protected function getOptionTargetFiles(InputInterface $input,
        OutputInterface $output
    ) {
        if ($this->isVeryVerbose($output)) {
            $output->writeln('Specific files mode.');
        }

        $files = array();
        foreach ($this->getAvailableHooks() as $hook) {
            if ($input->getOption($hook)) {
                $files[] = $hook;
            }
        }

        $userFile = $input->getOption('hook');
        if ($userFile) {
            if (!in_array($userFile, $this->getAvailableHooks())) {
                throw new Exception("Unknown commithook file '$userFile'.");
            }
            if (!in_array($userFile, $files)) {
                $files[] = $userFile;
                return $files;
            }
            return $files;
        }
        return $files;
    }

    /**
     * Get GIT hooks directory path
     *
     * @param OutputInterface $output
     * @param string          $projectDir
     * @return string
     * @throws Exception
     */
    protected function getHooksDir(OutputInterface $output, $projectDir)
    {
        $hooksDir = $projectDir . '/.git/hooks';
        if (!is_dir($hooksDir)) {
            throw new Exception('GIT hooks directory not found.');
        }
        return $hooksDir;
    }

    /**
     * Ask about GIT project root dir
     *
     * @param OutputInterface $output
     * @return array
     */
    protected function askProjectDir(OutputInterface $output)
    {
        $dir = $this->getCommandDir();
        $validator = function ($dir) {
            $dir = rtrim($dir, '\\/');
            return is_dir($dir . '/.git');
        };

        if ($validator($dir)) {
            return $dir;
        }

        do {
            $dir = $this->getDialog()->ask(
                $output, "Please set your root project directory [$dir]: ", $dir
            );
            if (!$validator($dir)) {
                $output->writeln(
                    'Sorry, selected directory does not contain ".git" directory.'
                );
                $dir = null;
            }
        } while (!$dir);

        return rtrim($dir, '\\/');
    }

    /**
     * Get CLI directory (pwd)
     *
     * @return string
     */
    protected function getCommandDir()
    {
        return $_SERVER['PWD'];
    }

    /**
     * Get dialog helper
     *
     * @return DialogHelper
     */
    protected function getDialog()
    {
        return $this->getHelperSet()->get('dialog');
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

    /**
     * Get config
     *
     * @param OutputInterface $output
     * @param bool            $cached
     * @return Config
     */
    public function getConfig(OutputInterface $output, $cached = true)
    {
        return Config::getInstance(array('file' => $this->commithookDir
            . DIRECTORY_SEPARATOR . 'LibHooks' . DIRECTORY_SEPARATOR . 'config.xml'));
    }
}
