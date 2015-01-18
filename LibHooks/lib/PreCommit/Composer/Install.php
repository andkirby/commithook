<?php
namespace PreCommit\Composer;

use Composer\Command\Helper\DialogHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * CommitHooks files installer
 *
 * @package PreCommit
 */
class Install extends Command
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
     * @param bool   $alone         Flag if command will run alone ie without application
     */
    public function __construct($commithookDir, $alone = false)
    {
        $this->commithookDir = $commithookDir;

        $this->_initCommand();
        if ($alone) {
            $this->_initDefaultHelpers();
        }
        parent::__construct();
    }

    /**
     * Init default helpers
     *
     * @return $this
     */
    protected function _initCommand()
    {
        $this->setName('install');
        $this->setHelp(
            'This command can install available GIT hook files into your project.'
        );
        $this->setDescription(
            'This command can install available GIT hook files into your project.'
        );
        return $this;
    }

    /**
     * Init default helpers
     *
     * @return $this
     */
    protected function _initDefaultHelpers()
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
     * @param InputInterface   $input
     * @param OutputInterface  $output
     * @return int|null|void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $hooksDir = $this->getHooksDir(
            $output, $this->askProjectDir($output)
        );
        $this->createHooks(
            $output,
            $hooksDir,
            $this->askPhpPath($output),
            $this->getRunnerFile()
        );

        $output->writeln(
            "PHP CommitHook files have been created in '$hooksDir'."
        );
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
            if (!$this->getDialog()->askConfirmation(
                $output,
                "GIT hooks directory does not exist. Would you like to create hooks directory?"
            )
            ) {
                throw new Exception('Could not create directory ' . $hooksDir);
            }
            mkdir($hooksDir, 0770, false);
        }
        return $hooksDir;
    }

    /**
     * @param OutputInterface $output
     * @param string          $hooksDir
     * @param string          $phpPath
     * @param string          $runnerPath
     * @return $this
     * @throws Exception
     */
    protected function createHooks(OutputInterface $output, $hooksDir, $phpPath, $runnerPath)
    {
        $body = $this->getHookBody($phpPath, $runnerPath);
        foreach ($this->getAvailableHooks() as $hook) {
            $this->createHookFile(
                $output, $hooksDir . DIRECTORY_SEPARATOR . $hook, $body
            );
        }
        return $this;
    }

    /**
     * Create hook file
     *
     * @param OutputInterface $output
     * @param string          $file
     * @param string          $body
     * @return $this
     * @throws Exception
     */
    protected function createHookFile(OutputInterface $output, $file, $body)
    {
        if (file_exists($file)
            && !$this->getDialog()->askConfirmation(
                $output, "File '$file' already exists. Overwrite it?"
            )
        ) {
            throw new Exception('Could not overwrite file ' . $file);
        }
        if (!file_put_contents($file, $body)) {
            throw new Exception('Could not create file ' . $file);
        }
        return $this;
    }

    /**
     * Ask about PHP executable file
     *
     * @param OutputInterface $output
     * @return array
     */
    protected function askPhpPath(OutputInterface $output)
    {
        $validator = function ($file) {
            return is_file($file);
        };
        $file = $this->getSystemPhpPath();

        if ($validator($file)) {
            return $file;
        }

        do {
            $file = $this->getDialog()->ask(
                $output, "Please set your PHP executable file [$file]: ", $file
            );
            if (!$validator($file)) {
                $output->writeln('Given PHP executable file does not exists.');
                $file = null;
            }
        } while (!$file);

        return rtrim($file, '\\/');
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
     * Get commithook file template
     *
     * @return string
     */
    protected function getHookTemplate()
    {
        return <<<PHP
#!/usr/bin/env {PHP_EXE}
<?php
\$hookName = __FILE__;
require_once '{RUNNER_PHP}';
PHP;
    }

    /**
     * Get commithook file body
     *
     * @param string $phpPath
     * @param string $runnerPhpPath
     * @return string
     */
    protected function getHookBody($phpPath, $runnerPhpPath)
    {
        $phpPath        = $this->normalizePath($phpPath);
        $runnerPhpPath  = $this->normalizePath($runnerPhpPath);
        $template       = $this->getHookTemplate();
        $template       = str_replace('{PHP_EXE}', $phpPath, $template);
        return str_replace('{RUNNER_PHP}', $runnerPhpPath, $template);
    }

    /**
     * Get path to commithook checking runner PHP file
     *
     * @return string
     */
    protected function getRunnerFile()
    {
        return $this->commithookDir . '/LibHooks/runner.php';
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
     * Get system path to executable PHP file
     *
     * @return null|string
     */
    protected function getSystemPhpPath()
    {
        if (defined('PHP_BIN_DIR') && (is_file(PHP_BIN_DIR . '/php'))) {
            return PHP_BIN_DIR . '/php';
        } elseif (defined('PHP_BIN_DIR') && (is_file(PHP_BIN_DIR . '/php.exe'))) {
            return PHP_BIN_DIR . '/php.exe';
        } elseif (defined('PHP_BINARY') && is_file(PHP_BINARY)) {
            return PHP_BINARY;
        }
        return null;
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
}
