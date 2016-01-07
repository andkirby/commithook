<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
namespace PreCommit\Validator\Linter;

use PreCommit\Config;
use PreCommit\Exception;
use PreCommit\Interpreter\InterpreterInterface;
use PreCommit\Validator\AbstractValidator;

/**
 * Class AbstractLintValidator
 *
 * This base class designed for external code linters
 *
 * @package PreCommit\Validator\Linter
 */
abstract class AbstractLintValidator extends AbstractValidator
{
    /**
     * Command for running linter
     *
     * @var string
     */
    protected $command;

    /**
     * Validate XML
     *
     * @param string $content
     * @param string $file
     * @return bool
     */
    public function validate($content, $file)
    {
        $status = false;
        foreach ($this->runLinter($file) as $errors) {
            foreach ($errors as $error) {
                $status  = true;
                $message = $error[0];
                $line    = null;
                if (isset($error[1]) && isset($error[2])) {
                    $line = $error[1].':'.$error[2];
                } elseif (isset($error[1])) {
                    $line = $error[1];
                }
                $this->addError(
                    $file,
                    $this->getDefaultErrorCode(),
                    $message,
                    $line
                );
            }
        }

        return $status;
    }

    /**
     * Validate file with linter
     *
     * @param string $file
     * @return array
     */
    protected function runLinter($file)
    {
        $command = $this->getCommand($file);
        if (!$command) {
            return array();
        }
        exec($command, $output);

        return $this->getOutputInterpreter()->interpret($output);
    }

    /**
     * Get default error code of linter errors
     *
     * @return string
     */
    abstract protected function getDefaultErrorCode();

    /**
     * Get complete command to execute linter validation
     *
     * @param string $file
     * @return string
     * @throws Exception
     */
    protected function getCommand($file)
    {
        $command = $this->getCompleteCommandTemplate();
        if (!$command) {
            return null;
        }

        return str_replace('%file%', $file, $command);
    }

    /**
     * Get linter output interpreter
     *
     * @return InterpreterInterface
     */
    abstract protected function getOutputInterpreter();

    /**
     * Get complete command template
     *
     * Get updated template with set up all parameters excepting "file"
     *
     * @return bool|string
     * @throws \PreCommit\Exception
     */
    protected function getCompleteCommandTemplate()
    {
        if (null !== $this->command) {
            return $this->command;
        }
        $command = $this->getCommandTemplate();

        if (!$command) {
            throw new Exception('No command template for running \''.$this->getValidatorCode().'\' validator.');
        }

        $interpreterPath = $this->getInterpreterPath();
        $linterPath      = $this->getLinterPath();
        $configPath      = $this->getConfigPath();

        if (!$interpreterPath || !$linterPath || !$configPath && strpos($command, '%config%')) {
            //the linter is not configured
            $this->command = '';

            return '';
        }

        $command       = str_replace('%interpreter%', $interpreterPath, $command);
        $command       = str_replace('%config%', $configPath, $command);
        $this->command = str_replace('%linter%', $linterPath, $command);

        return $this->command;
    }

    /**
     * Get command template
     *
     * @return null|string
     */
    protected function getCommandTemplate()
    {
        return $this->getConfig()->getNode('validators/'.$this->getValidatorCode().'/execution/command');
    }

    /**
     * Get validator code
     *
     * @return string
     */
    abstract protected function getValidatorCode();

    /**
     * Get path to NodeJS executable file
     *
     * @return null|string
     */
    protected function getInterpreterPath()
    {
        $code = $this->getConfig()->getNode('validators/'.$this->getValidatorCode().'/execution/interpreter_type');

        return $code ? $this->getConfig()->getNode('code/interpreter/'.$code) : null;
    }

    /**
     * Get path to linter executable file
     *
     * @return null|string
     */
    protected function getLinterPath()
    {
        return $this->getConfig()->getNode('validators/'.$this->getValidatorCode().'/execution/linter');
    }

    /**
     * Get path to linter config file
     *
     * @return null|string
     */
    protected function getConfigPath()
    {
        /**
         * Read custom file
         */
        $file = $this->getCustomConfigFile();

        /**
         * Try to use predefined files
         */
        if (!$file) {
            $file = $this->getPredefinedConfigFile();
        }

        /**
         * Use default config file
         */
        if (!$file) {
            $file = $this->getDefaultConfigFile();
        }

        return ($file && is_file($file)) ? $file : null;
    }

    /**
     * Get config model
     *
     * @return Config
     */
    protected function getConfig()
    {
        return Config::getInstance();
    }

    /**
     * Get linter custom config file
     *
     * @return null|string
     */
    protected function getCustomConfigFile()
    {
        $file = $this->getConfig()->getNode('validators/'.$this->getValidatorCode().'/config/custom');
        if ($file) {
            $file = $this->normalizePath($file);
            if (0 !== strpos($file, 'PROJECT_DIR/')
                && 0 !== strpos($file, 'HOME/')
                && (0 !== strpos($file, '/') || false !== strpos($file, ':/'))
            ) {
                $file = "PROJECT_DIR/$file";
            }
            $file = $this->getConfig()->readPath($file);
        }

        return $file;
    }

    /**
     * Get linter predefined config file
     *
     * @return null|string
     */
    protected function getPredefinedConfigFile()
    {
        $file       = null;
        $predefined = $this->getConfig()->getMultiNode(
            'validators/'.$this->getValidatorCode().'/config/file/predefined'
        );
        if ($predefined) {
            foreach ($predefined as $file) {
                $file = $this->normalizePath($file);
                $file = realpath($this->getConfig()->readPath($file)) ?: null;
                if ($file) {
                    //file found
                    break;
                }
            }
        }

        return $file;
    }

    /**
     * Get linter default config file
     *
     * @return null|string
     */
    protected function getDefaultConfigFile()
    {
        $file = $this->getConfig()->getNode('validators/'.$this->getValidatorCode().'/config/file/default');
        if ($file) {
            $file = $this->normalizePath(
                $this->getConfig()->readPath($file)
            );
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
        return str_replace('\\', '/', $path);
    }
}
