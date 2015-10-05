<?php
namespace PreCommit\Composer\Command\Install;

use PreCommit\Composer\Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * CommitHooks files installer
 *
 * @package PreCommit
 */
class Install extends CommandAbstract
{
    /**
     * Init default helpers
     *
     * @return $this
     */
    protected function configureCommand()
    {
        $this->setName('install');
        $this->setHelp(
            'This command can install available hook files into your project.'
        );
        $this->setDescription(
            'This command can install available hook files into your project.'
        );
        return $this;
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
            'overwrite', '-w', InputOption::VALUE_NONE,
            'Overwrite exist hook files.'
        );
        $this->addOption(
            'php-binary', '-p', InputOption::VALUE_REQUIRED,
            'Path to PHP binary file.'
        );
        return $this;
    }

    /**
     * Execute command
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return int
     * @throws Exception
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);
        try {
            $hooksDir = $this->getHooksDir(
                $output, $this->askProjectDir($input, $output)
            );
            $this->createHooks(
                $output, $input,
                $hooksDir,
                $this->getTargetFiles($input, $output),
                $this->askPhpPath($input, $output),
                $this->getRunnerFile()
            );
        } catch (Exception $e) {
            if ($this->isVeryVerbose($output)) {
                throw $e;
            } else {
                $output->writeln($e->getMessage());
                return 1;
            }
        }

        if ($this->isVerbose($output)) {
            $output->writeln(
                "PHP CommitHook files have been created in '$hooksDir'."
            );
        } else {
            $output->writeln(
                "PHP CommitHook files have been created."
            );
        }
        return 0;
    }

    /**
     * Create hook files
     *
     * @param OutputInterface $output
     * @param InputInterface  $input
     * @param string          $hooksDir
     * @param array           $targetHooks
     * @param string          $phpPath
     * @param string          $runnerPath
     * @return $this
     * @throws Exception
     */
    protected function createHooks(OutputInterface $output, InputInterface $input, $hooksDir,
        $targetHooks, $phpPath, $runnerPath
    ) {
        $body = $this->getHookBody($phpPath, $runnerPath);
        foreach ($targetHooks as $file) {
            $this->createHookFile(
                $output, $input, $hooksDir . DIRECTORY_SEPARATOR . $file, $body
            );
        }
        return $this;
    }

    /**
     * Create hook file
     *
     * @param OutputInterface $output
     * @param InputInterface  $input
     * @param string          $file
     * @param string          $body
     * @return $this
     * @throws Exception
     */
    protected function createHookFile(OutputInterface $output, InputInterface $input, $file, $body)
    {
        if (!$input->getOption('overwrite') && file_exists($file)
            && 'y' !== $this->getQuestionHelper()->ask(
                $input, $output,
                $this->getQuestionConfirm("File '$file' already exists. Overwrite it?")
            )
        ) {
            $output->writeln('Could not overwrite file ' . $file);
            return $this;
        }
        if (!file_put_contents($file, $body)) {
            throw new Exception('Could not create file ' . $file);
        }
        chmod($file, 0777);
        if ($this->isVerbose($output)) {
            $output->writeln("CommitHook file set to '$file'.");
        }
        return $this;
    }

    /**
     * Ask about PHP executable file
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return array
     * @throws \PreCommit\Composer\Exception
     */
    protected function askPhpPath(InputInterface $input, OutputInterface $output)
    {
        $validator = $this->getPhpValidator();

        $file = $input->getOption('php-binary');
        if (!$file) {
            $file = $this->getSystemPhpPath();
        }

        $max = 3;
        $i = 0;
        while (!$file || !$validator($file, $output)) {
            if ($file) {
                $output->writeln('Given PHP executable file is not valid.');
            }
            $file = $this->getQuestionHelper()->ask(
                $input, $output,
                $this->getQuestion("Please set your PHP executable file", $file)
            );
            if (++$i > $max) {
                throw new Exception('Path to PHP executable file is not set.');
            }
        }

        return $file;
    }

    /**
     * Get PHP binary file validator
     *
     * @return callable
     */
    protected function getPhpValidator()
    {
        return function ($file, OutputInterface $output = null) {
            if (is_file($file)) {
                $test = `$file -r "echo 'Test passed.';" 2>&1`;
                if ($output && $output->getVerbosity() >= OutputInterface::VERBOSITY_VERY_VERBOSE) {
                    $output->writeln(
                        'PHP test output: ' . PHP_EOL . $test
                    );
                }
                return 0 === strpos($test, 'Test passed.');
            }
            return false;
        };
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
        $phpPath = $this->normalizePath($phpPath);
        $runnerPhpPath = $this->normalizePath($runnerPhpPath);
        $template = $this->getHookTemplate();
        $template = str_replace('{PHP_EXE}', $phpPath, $template);
        return str_replace('{RUNNER_PHP}', $runnerPhpPath, $template);
    }

    /**
     * Get path to commithook checking runner PHP file
     *
     * @return string
     */
    protected function getRunnerFile()
    {
        return $this->commithookDir . '/bin/runner.php';
    }

    /**
     * Get system path to executable PHP file
     *
     * @return null|string
     */
    public function getSystemPhpPath()
    {
        $file = null;
        if (defined('PHP_BIN_DIR') && is_file(PHP_BIN_DIR . '/php')) {
            $file = PHP_BIN_DIR . '/php';
        } elseif (defined('PHP_BIN_DIR') && is_file(PHP_BIN_DIR . '/php.exe')) {
            $file = PHP_BIN_DIR . '/php.exe';
        } elseif (defined('PHP_BINARY') && is_file(PHP_BINARY)) {
            $file = PHP_BINARY;
        } elseif (getenv('PHP_BINARY') && is_file(getenv('PHP_BINARY'))) {
            $file = getenv('PHP_BINARY');
        } elseif (isset($_SERVER['_']) && pathinfo($_SERVER['_'], PATHINFO_FILENAME) == 'php') {
            $file = $_SERVER['_'];
        } elseif (is_file('/usr/local/bin/php')) {
            //try to check Unix system php file
            $file = '/usr/local/bin/php';
        }
        if ($file) {
            $file = str_replace('/', DIRECTORY_SEPARATOR, $file);
        }
        return $file;
    }

    /**
     * Normalize filesystem path
     *
     * @param string $path
     * @return string mixed
     */
    protected function normalizePath($path)
    {
        if (DIRECTORY_SEPARATOR == '/') {
            return str_replace('\\', DIRECTORY_SEPARATOR, $path);
        }
        return str_replace('/', DIRECTORY_SEPARATOR, $path);
    }

    /**
     * Get custom hook option description
     *
     * @return string
     */
    protected function getCustomHookOptionDescription()
    {
        return 'Set specific hook file to install.';
    }

    /**
     * Get hook option description
     *
     * @param string $hook
     * @return string
     */
    protected function getHookOptionDescription($hook)
    {
        return "Set '$hook' hook file to install.";
    }
}
