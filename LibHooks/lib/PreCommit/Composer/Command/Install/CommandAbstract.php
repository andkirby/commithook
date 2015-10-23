<?php
namespace PreCommit\Composer\Command\Install;

use PreCommit\Composer\Command;
use PreCommit\Composer\Exception;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Base "install" command abstract class
 *
 * @package PreCommit\Composer\Command
 */
abstract class CommandAbstract extends Command\CommandAbstract
{
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
     * Init input definitions
     *
     * @return $this
     */
    protected function configureInput()
    {
        parent::configureInput();
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
}
