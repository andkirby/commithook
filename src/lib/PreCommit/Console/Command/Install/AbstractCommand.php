<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
namespace PreCommit\Console\Command\Install;

use PreCommit\Console\Command;
use PreCommit\Console\Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * Base "install" command abstract class
 *
 * @package PreCommit\Console\Command
 */
abstract class AbstractCommand extends Command\AbstractCommand
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
        return ['commit-msg', 'pre-commit'];
    }

    /**
     * Get target files
     *
     * @return array
     * @throws Exception
     */
    protected function getTargetFiles()
    {
        if (!$this->isAskedSpecificFile($this->input)) {
            if ($this->isVeryVerbose()) {
                $this->output->writeln('All files mode.');
            }

            return $this->getAvailableHooks();
        }

        return $this->getOptionTargetFiles();
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
     * @return array
     * @throws Exception
     */
    protected function getOptionTargetFiles()
    {
        if ($this->isVeryVerbose()) {
            $this->output->writeln('Specific files mode.');
        }
        $files = [];
        foreach ($this->getAvailableHooks() as $hook) {
            if ($this->input->getOption($hook)) {
                $files[] = $hook;
            }
        }

        $userFile = $this->input->getOption('hook');
        if ($userFile) {
            if (!in_array($userFile, $this->getAvailableHooks())) {
                throw new Exception("Unknown commithook file '$userFile'.");
            }
            if (!in_array($userFile, $files)) {
                $files[] = $userFile;
            }
        }

        return $files;
    }

    /**
     * Get GIT hooks directory path
     *
     * @param string $projectDir
     * @return string
     * @throws Exception
     */
    protected function getHooksDir($projectDir)
    {
        $hooksDir = $this->getProjectDirHelper()
            ->getHooksDirectory($projectDir);
        if (!is_dir($hooksDir)) {
            throw new Exception("Git hooks directory '$hooksDir' not found.");
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
            'hook',
            null,
            InputOption::VALUE_REQUIRED,
            $this->getCustomHookOptionDescription()
        );
        foreach ($this->getAvailableHooks() as $hook) {
            $this->addOption(
                $hook,
                null,
                InputOption::VALUE_NONE,
                $this->getHookOptionDescription($hook)
            );
        }

        return $this;
    }
}
