<?php
namespace PreCommit\Composer;

use Composer\Command\Helper\DialogHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * CommitHooks files remover
 *
 * @package PreCommit\Composer
 */
class Remove extends Command
{
    /**
     * Base commithook directory
     *
     * @var null|string
     */
    protected $commithookDir;

    /**
     * Construct
     * Set commithook directory.
     * Set dialog helper.
     * Set console name.
     *
     * @param string $commithookDir
     * @param bool   $alone Flag if command will run alone ie without application
     */
    public function __construct($commithookDir, $alone = false)
    {
        $this->commithookDir = $commithookDir;

        $this->initCommand();
        if ($alone) {
            $this->initDefaultHelpers();
        }
        parent::__construct();

        $this->initInputDefinition();
    }

    /**
     * Init default helpers
     *
     * @return $this
     */
    protected function initCommand()
    {
        $this->setName('remove');
        $this->setHelp(
            'This command can remove installed hook files in your project.'
        );
        $this->setDescription(
            'This command can remove installed hook files in your project.'
        );
        return $this;
    }

    /**
     * Init default helpers
     *
     * @return $this
     */
    protected function initDefaultHelpers()
    {
        $this->setHelperSet(
            new HelperSet(array('dialog' => new DialogHelper()))
        );
        return $this;
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
     * Execute command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $hooksDir = $this->getHooksDir(
            $output, $this->askProjectDir($output)
        );

        $files = $this->getTargetFiles();

        $this->removeHookFiles($output, $hooksDir, $files);

        return 0;
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
     * Get available hooks in CommitHooks application
     *
     * @return array
     */
    protected function getAvailableHooks()
    {
        return array('commit-msg', 'pre-commit');
    }

    /**
     * Normalize filesystem path
     *
     * @param string $path
     * @return string mixed
     */
    protected function normalizePath($path)
    {
        return str_replace('\\', '/', $path);
    }

    /**
     * Init input definitions
     *
     * @return $this
     */
    protected function initInputDefinition()
    {
        $definition = $this->getDefinition();
        $definition->addOption(
            new InputOption(
                '--hook', '-h', InputOption::VALUE_REQUIRED,
                'Set specific hook file to remove.'
            )
        );
        return $this;
    }

    /**
     * @return array
     * @throws \PreCommit\Composer\Exception
     */
    protected function getTargetFiles()
    {
        $files = $this->getAvailableHooks();
        $userFile = $this->getDefinition()->getArgument('--hook');
        if ($userFile) {
            if (!in_array($userFile, $files)) {
                throw new Exception("Unknown commithook file '$userFile'.");
            }
            $files = array($userFile);
            return $files;
        }
        return $files;
    }

    /**
     * @param OutputInterface $output
     * @param string          $hooksDir
     * @param array           $files
     * @return $this
     */
    protected function removeHookFiles(OutputInterface $output, $hooksDir,
        array $files
    ) {
        foreach ($files as $filename) {
            $file = $hooksDir . PATH_SEPARATOR . $filename;
            if (!is_file($file) && $output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                //file not found
                $output->writeln("Hook file '$filename' not found. Skipped.");
            } else {
                if (!unlink($file)) {
                    //cannot remove
                    $output->writeln("Hook file '$filename' cannot be removed. Skipped.");
                } elseif ($output->getVerbosity()) {
                    //success removing
                    $output->writeln("Hook file '$filename' has removed.");
                }
            }
        }
        return $this;
    }
}
