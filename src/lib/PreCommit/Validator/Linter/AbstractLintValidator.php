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
     * Validate XML
     *
     * @param string $content
     * @param string $file
     * @return bool
     */
    public function validate($content, $file)
    {
        foreach ($this->runLinter($file) as $file => $errors) {
            foreach ($errors as $error) {
                $line    = $error[0].':'.$error[1];
                $message = $error[2];
                $this->addError(
                    $file,
                    $this->getDefaultErrorCode(),
                    $message,
                    $line
                );
            }
        }

        return !$this->errorCollector->hasErrors();
    }

    /**
     * Validate file with JSHint
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

        return $this->getLinterOutputInterpreter()->interpret($output);
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
        $command = $this->getCommandFormat();

        if (!$command) {
            throw new Exception('No command format for running \''.$this->getValidatorCode().'\' validator.');
        }

        $interpreterPath = $this->getInterpreterPath();
        $linterPath      = $this->getLinterPath();
        $configPath      = $this->getLinterConfigPath();

        if (!$interpreterPath || !$linterPath || !$configPath && strpos($command, '%config%')) {
            //the linter is not configured
            return null;
        }

        $command = str_replace('%interpreter%', $interpreterPath, $command);
        $command = str_replace('%config%', $configPath, $command);
        $command = str_replace('%linter%', $linterPath, $command);
        $command = str_replace('%file%', $file, $command);

        return $command;
    }

    /**
     * Get linter output interpreter
     *
     * @return InterpreterInterface
     */
    abstract protected function getLinterOutputInterpreter();

    /**
     * Get path to linter executable file by interpreter
     *
     * @return null|string
     */
    protected function getCommandFormat()
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
     * Get path to interpreter executable file
     *
     * @return null|string
     */
    abstract protected function getInterpreterPath();

    /**
     * Get path to linter executable file
     *
     * @return null|string
     */
    abstract protected function getLinterPath();

    /**
     * Get path to linter executable file by interpreter
     *
     * @return null|string
     */
    abstract protected function getLinterConfigPath();

    /**
     * Get config model
     *
     * @return Config
     */
    protected function getConfig()
    {
        return Config::getInstance();
    }
}
