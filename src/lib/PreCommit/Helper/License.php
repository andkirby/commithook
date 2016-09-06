<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */

namespace PreCommit\Helper;

use PreCommit\Config;

/**
 * Class to validate license block in files
 *
 * @package PreCommit\Validator
 */
class License
{
    /**
     * Path matcher
     *
     * @var PathMatch
     */
    protected $matcher;

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
     * Get paths to files which should contain licences
     *
     * @param string $type
     * @return array
     */
    public function getPaths($type)
    {
        if (!$this->getLicenseName()) {
            return [];
        }

        $paths = array_values(
            $this->getConfig()->getNodeArray('validators/License/licenses/'.$this->getLicenseName().'/paths/'.$type)
        );
        foreach ($paths as &$path) {
            $path = rtrim($path, '/').'/';
        }

        return $paths;
    }

    /**
     * Check if license required for a file
     *
     * @param string $file
     * @return bool
     */
    public function isLicenseRequired($file)
    {
        return $this->getPathMatcher()->test($file);
    }

    /**
     * Check if content contains license test string
     *
     * @param string $content
     * @return bool
     */
    public function contentHasLicense($content)
    {
        return false !== strpos($content, $this->getTestLicense());
    }

    /**
     * Get path matcher
     *
     * @return PathMatch
     */
    protected function getPathMatcher()
    {
        if ($this->matcher === null) {
            $this->matcher = new PathMatch();

            $this->matcher->setAllowedByDefault(false)
                ->setAllowed(
                    $this->getPaths('required')
                )
                ->setProtected(
                    $this->getPaths('ignored')
                );
        }

        return $this->matcher;
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
