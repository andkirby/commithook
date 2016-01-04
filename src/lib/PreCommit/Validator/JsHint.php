<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
namespace PreCommit\Validator;

use PreCommit\Config;
use PreCommit\Exception;
use PreCommit\Interpreter\JsHintOutput;

/**
 * Class JsHint
 *
 * This class responsible of getting errors which can be Javascript files by JSHint code sniffer
 *
 * @package PreCommit\Validator
 */
class JsHint extends AbstractValidator
{
    /**#@+
     * Error codes
     */
    const CODE_JSHINT_ERROR = 'jsHintError';
    /**#@-*/

    /**
     * Error messages
     *
     * @var array
     */
    protected $errorMessages
        = array(
            self::CODE_JSHINT_ERROR => '(jshint) %value%',
        );

    /**
     * Validate XML
     *
     * @param string $content
     * @param string $file
     * @return bool
     */
    public function validate($content, $file)
    {
        foreach ($this->validateByJsHint($file) as $file => $errors) {
            foreach ($errors as $error) {
                $line    = $error[0].':'.$error[1];
                $message = $error[2];
                $this->addError(
                    $file,
                    self::CODE_JSHINT_ERROR,
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
    protected function validateByJsHint($file)
    {
        $command = $this->getCommand($file);
        if (!$command) {
            return array();
        }
        exec($command, $output);

        $interpreter = new JsHintOutput();

        return $interpreter->interpret($output);
    }

    /**
     * Get complete command to execute JSHint validation
     *
     * @param string $file
     * @return string
     * @throws Exception
     */
    protected function getCommand($file)
    {
        $interpreterPath = $this->getNodeJsPath();
        $jshintPath      = $this->getJsHintPath();
        $configPath      = $this->getJsHintConfigPath();

        if (!$interpreterPath || !$jshintPath || !$configPath) {
            return array();
        }

        $command = $this->getCommandFormat();

        if (!$command) {
            throw new Exception('No command for running jshint.');
        }

        $command = str_replace('%interpreter%', $interpreterPath, $command);
        $command = str_replace('%config%', $configPath, $command);
        $command = str_replace('%jshint%', $jshintPath, $command);
        $command = str_replace('%file%', $file, $command);

        return $command;
    }

    /**
     * Get path to NodeJS executable file
     *
     * @return null|string
     */
    protected function getNodeJsPath()
    {
        return $this->getConfig()->getNode('code/interpreter/nodejs');
    }

    /**
     * Get path to JsHint executable file
     *
     * @return null|string
     */
    protected function getJsHintPath()
    {
        return $this->getConfig()->getNode('validators/JsHint/execution/jshint');
    }

    /**
     * Get path to JsHint executable file by NodeJS
     *
     * @return null|string
     */
    protected function getJsHintConfigPath()
    {
        /**
         * Read custom project file
         */
        $configFile = $this->getConfig()->getNode('validators/JsHint/config/custom');
        if ($configFile) {
            $configFile = $this->normalizePath($configFile);
            if (0 !== strpos($configFile, 'PROJECT_DIR')
                && (0 !== strpos($configFile, '/') || false !== strpos($configFile, ':/'))
            ) {
                $configFile = "PROJECT_DIR/$configFile";
            }
        }

        /**
         * Try to use .jshintrc or packages.json
         */
        if (!$configFile) {
            $prjRoot = $this->getConfig()->getProjectDir();
            if (realpath("$prjRoot/.jshintrc")) {
                $configFile = "$prjRoot/.jshintrc";
            } elseif (realpath("$prjRoot/packages.json")) {
                $configFile = "$prjRoot/packages.json";
            }
        }

        /**
         * Use default config file
         */
        if (!$configFile) {
            $configFile = $this->getConfig()->getNode('validators/JsHint/config/file/default');
            $configFile = $this->normalizePath($configFile);
        }

        //make path absolute
        $configFile = $configFile ? $this->getConfig()->readPath($configFile) : null;

        return ($configFile && is_file($configFile)) ? $configFile : null;
    }

    /**
     * Get path to JsHint executable file by NodeJS
     *
     * @return null|string
     */
    protected function getCommandFormat()
    {
        return $this->getConfig()->getNode('validators/JsHint/execution/command');
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
