<?php
namespace PreCommit\Filter;

use PreCommit\Config;
use PreCommit\Exception;
use PreCommit\Filter\License\AbstractAdapter;
use PreCommit\Processor\PreCommit;

/**
 * Class License for adding license block into files
 *
 * @package PreCommit\Filter
 */
class License implements FilterInterface
{
    /**
     * {@inheritdoc}
     */
    public function filter($content, $file = null)
    {
        $newContent = $this->getLicenseGenerator($file)
            ->setContent($content)
            ->setLicense($this->getLicense())
            ->setTestLicense($this->getTestLicense())
            ->generate();

        if (!$newContent) {
            return $content;
        }

        $this->writeContent(
            $this->getFileAbsolutePath($file),
            $newContent
        );

        $this->addFileToVcs($file);

        return $content;
    }

    /**
     * Get adapter of license generator
     *
     * @param string $file
     * @return AbstractAdapter
     */
    public function getLicenseGenerator($file)
    {
        $ext = pathinfo($file, PATHINFO_EXTENSION);
        $class = __NAMESPACE__.'\\'.__CLASS__.'\\'.ucfirst($ext);

        return new $class();
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
     * Get license text block
     *
     * @return null|string
     */
    public function getLicense()
    {
        return trim($this->getConfig()->getNode(
            'validators/License/licenses/'.$this->getLicenseName().'/text'
        ));
    }

    /**
     * Get license text block
     *
     * @return null|string
     */
    public function getTestLicense()
    {
        return trim($this->getConfig()->getNode(
            'validators/License/licenses/'.$this->getLicenseName().'/test_text'
        ));
    }

    /**
     * Write content
     *
     * @param string $file
     * @param string $content
     * @return $this
     * @throws Exception
     */
    protected function writeContent($file, $content)
    {
        if (!file_put_contents($file, $content)) {
            throw new Exception('Cannot write content with license to file '.$file);
        }

        return $this;
    }

    /**
     * Get absolute path to file
     *
     * @param string $file
     * @return string
     */
    protected function getFileAbsolutePath($file)
    {
        return $this->getConfig()->getProjectDir().DIRECTORY_SEPARATOR.$file;
    }

    /**
     * Add file to VCS
     *
     * @param string $file
     * @return $this
     */
    protected function addFileToVcs($file)
    {
        PreCommit::getVcsAdapter()->addPath($file);

        return $this;
    }

    /**
     * Get license name
     *
     * @return null|string
     */
    protected function getLicenseName()
    {
        return $this->getConfig()->getNode('license/name');
    }
}