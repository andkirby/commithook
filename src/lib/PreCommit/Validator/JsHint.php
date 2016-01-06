<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
namespace PreCommit\Validator;

use PreCommit\Interpreter\JsHintOutput;
use PreCommit\Validator\Linter\AbstractLintValidator;

/**
 * Class JsHint
 *
 * This class responsible of getting errors which can be Javascript files by JSHint code sniffer
 *
 * @package PreCommit\Validator
 */
class JsHint extends AbstractLintValidator
{
    /**
     * Linter type
     */
    const TYPE = 'jshint';
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
     * Get path to NodeJS executable file
     *
     * @return null|string
     */
    protected function getInterpreterPath()
    {
        return $this->getConfig()->getNode('code/interpreter/nodejs');
    }

    /**
     * Get path to JsHint executable file
     *
     * @return null|string
     */
    protected function getLinterPath()
    {
        return $this->getConfig()->getNode('validators/JsHint/execution/jshint');
    }

    /**
     * Get path to JsHint executable file by NodeJS
     *
     * @return null|string
     */
    protected function getLinterConfigPath()
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
     * {@inheritdoc}
     */
    protected function getLinterOutputInterpreter()
    {
        return new JsHintOutput();
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultErrorCode()
    {
        return self::CODE_JSHINT_ERROR;
    }

    /**
     * {@inheritdoc}
     */
    protected function getValidatorCode()
    {
        return 'JsHint';
    }
}
