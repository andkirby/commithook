<?php
namespace PreCommit\Validator;

use PreCommit\Config;
use PreCommit\Exception;

/**
 * Class CodingStandard validator
 *
 * @package PreCommit\Validator
 */
class CodeSniffer extends AbstractValidator
{
    /**#@+
     * Error codes
     */
    const CODE_PHP_CODE_SNIFFER_ERROR = 'phpCodeSniffGeneralError';
    const CODE_PHP_CODE_SNIFFER_WARNING = 'phpCodeSniffGeneralWarning';
    /**#@-*/

    /**
     * Error messages
     *
     * @var array
     */
    protected $errorMessages
        = array(
            self::CODE_PHP_CODE_SNIFFER_ERROR => "CodeSniffer Error.",
        );

    /**
     * Validate content
     *
     * @param string $content
     * @param string $file
     * @return bool
     * @throws \Exception
     * @throws \PHP_CodeSniffer_Exception
     * @throws \PreCommit\Exception
     * @throws \PreCommit\Validator\CodeSniffer\Exception
     */
    public function validate($content, $file)
    {
        $collector = new CodeSniffer\Collector();
        $result    = $collector->process(
            array(
                'standard' => $this->getStandardConfigDir(),
                'files'    => array($file),
            )
        );

        if (!$this->getStandardConfigDir()) {
            throw new Exception(
                'CodeSniffer standard not found. Please set it by XPath validators/CodeSniffer/config_rule'
            );
        }

        $result = array_shift($result); //get result for the only file
        if ($result) {
            foreach (array('errors', 'warnings') as $type) {
                foreach ($result[$type] as $line => $error) {
                    foreach ($error as $position => $info) {
                        foreach ($info as $item) {
                            $typeLetter = strtoupper($type[0]);
                            $message    = "(phpcs {$typeLetter}) {$item['message']}";
                            if ($this->showErrorSource()) {
                                $message .= " ({$item['source']})";
                            }
                            $this->errorCollector->addError(
                                $file,
                                self::CODE_PHP_CODE_SNIFFER_ERROR,
                                $message,
                                null,
                                "$line:$position"
                            );
                        }
                    }
                }
            }
        }

        return !$this->errorCollector->hasErrors();
    }

    /**
     * Get status of showing source
     *
     * @return bool
     */
    protected function showErrorSource()
    {
        return true;
    }

    /**
     * Get code sniffer standard
     *
     * @return string
     */
    protected function getStandard()
    {
        $standard = $this->getStandardConfigDir();
        if (!$standard) {
            $standard = $this->getStandardName();
        }

        return $standard;
    }

    /**
     * Get standard config file
     *
     * @return string
     */
    protected function getStandardName()
    {
        return Config::getInstance()->getNode('validators/CodeSniffer/rule/name');
    }

    /**
     * Get standard config file
     *
     * @return string
     */
    protected function getStandardConfigDir()
    {
        $dir = Config::getInstance()->getNode('validators/CodeSniffer/rule/directory');
        if (realpath($dir)) {
            return $dir;
        }

        $dir = Config::getProjectDir().DIRECTORY_SEPARATOR.trim($dir, '\\/');
        if (realpath($dir)) {
            return $dir;
        }

        return null;
    }
}
