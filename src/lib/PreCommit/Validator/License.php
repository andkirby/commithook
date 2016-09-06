<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */

namespace PreCommit\Validator;

use PreCommit\Config;
use PreCommit\Helper\PathMatch;

/**
 * Class to validate license block in files
 *
 * @package PreCommit\Validator
 */
class License extends AbstractValidator
{
    /**#@+
     * Error codes
     */
    const CODE_MISSED_LICENSE = 'missedLicenseBlock';
    /**#@-*/

    /**
     * Error messages
     *
     * @var array
     */
    protected $errorMessages
        = [
            self::CODE_MISSED_LICENSE => 'Missed license block.',
        ];

    /**
     * Validate content
     *
     * @param string $content
     * @param string $file
     * @return bool
     */
    public function validate($content, $file)
    {
        if (!$this->isLicenseRequired($file)) {
            return true;
        }

        if (false === strpos($content, $this->getTestLicense())) {
            $this->addError($file, self::CODE_MISSED_LICENSE);
        }

        return !$this->errorCollector->hasErrors();
    }

    /**
     * Get license text block
     *
     * @return null|string
     */
    public function getTestLicense()
    {
        $test = trim(
            $this->getConfig()->getNode(
                'validators/License/licenses/'.$this->getLicenseName().'/test_text'
            )
        );

        return $test ?: $this->getLicense();
    }

    /**
     * Get license text block
     *
     * @return null|string
     */
    public function getLicense()
    {
        return trim(
            $this->getConfig()->getNode(
                'validators/License/licenses/'.$this->getLicenseName().'/text'
            )
        );
    }

    /**
     * Get license name
     *
     * @return null|string
     */
    public function getLicenseName()
    {
        return $this->getConfig()->getNode('license/name');
    }

    /**
     * Check
     *
     * @param string $file
     * @return bool
     */
    protected function isLicenseRequired($file)
    {
        $matcher = new PathMatch();

        return $matcher
            ->setAllowed(
                $this->getPaths('required')
            )
            ->setProtected(
                $this->getPaths('ignored')
            )
            ->test($file);
    }

    /**
     * Get paths to files which should contain licences
     *
     * @param string $type
     * @return array
     */
    protected function getPaths($type)
    {
        $name = $this->getLicenseName();

        $paths = array_values($this->getConfig()->getNodeArray('validators/License/licenses/'.$name.'/paths/'.$type));
        foreach ($paths as &$path) {
            $path = rtrim($path, '/').'/';
        }

        $filePaths = array_values(
            $this->getConfig()->getNodeArray('validators/License/licenses/'.$name.'/filepaths/'.$type)
        );

        return array_merge($paths, $filePaths);
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
}
