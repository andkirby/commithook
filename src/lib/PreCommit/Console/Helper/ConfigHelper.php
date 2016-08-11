<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */

namespace PreCommit\Console\Helper;

use PreCommit\Config as ConfigInstance;
use PreCommit\Exception;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Finder\Finder;

/**
 * Helper for writing config
 *
 * @package PreCommit\Console\Helper
 */
class ConfigHelper extends Helper
{
    /**
     * Helper name
     */
    const NAME = 'commithook_config';

    /**
     * Writer
     *
     * @var Config\WriterHelper
     */
    protected $writer;

    /**
     * Value to write
     *
     * Format : xpath => value
     *
     * @var array
     */
    protected $values = array();

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * Reset values
     *
     * @return $this
     */
    public function reset()
    {
        $this->values = array();

        return $this;
    }

    /**
     * Set XML value
     *
     * @param string $xpath
     * @param string $value
     * @return $this
     */
    public function setValue($xpath, $value)
    {
        $this->values[$xpath] = $value;

        return $this;
    }

    /**
     * Write values to config file
     *
     * @param string $configFile
     * @return bool
     */
    public function write($configFile)
    {
        $config = $this->loadConfig($configFile);

        $updated = false;
        foreach ($this->values as $xpath => $value) {
            if ($config->getNode($xpath) == $value) {
                continue;
            }
            $this->setValueToXml($config, $xpath, $value);
            $updated = true;
        }

        if ($updated) {
            $this->getWriter()->write(
                $config,
                $configFile
            );
        }
        $this->clearCache();

        return $updated;
    }

    /**
     * Clear cache
     *
     * @return $this
     * @throws Exception
     */
    public function clearCache()
    {
        /** @var ClearCacheHelper $cleaner */
        $cleaner = $this->getHelperSet()->get(ClearCacheHelper::NAME);
        $cleaner->clearConfigCache();

        return $this;
    }

    /**
     * Save value to XML
     *
     * @param ConfigInstance $config
     * @param string         $xpath
     * @param string         $value
     * @return $this
     */
    public function setValueToXml(ConfigInstance $config, $xpath, $value)
    {
        $this->getXmlMerger()->merge(
            $config,
            $this->getXmlUpdate($xpath, $value)
        );

        return $this;
    }

    /**
     * Write configuration value by XML path
     *
     * @param string $configFile
     * @param string $xpath
     * @param string $value
     * @return bool
     * @throws \PreCommit\Command\Exception
     */
    public function writeValue($configFile, $xpath, $value)
    {
        $config = $this->loadConfig($configFile);
        if ((string) $value === $config->getNode($xpath)) {
            return false;
        }

        $this->setValueToXml($config, $xpath, (string) $value);

        $this->getWriter()->write(
            $config,
            $configFile
        );
        $this->clearCache();

        return true;
    }

    /**
     * Load config
     *
     * @param string $file
     * @return ConfigInstance
     * @throws \PreCommit\Exception
     */
    protected function loadConfig($file)
    {
        if (!file_exists($file)) {
            $this->writeEmptyXmlFile($file);
        }

        return ConfigInstance::loadInstance(array('file' => $file), false);
    }

    /**
     * Get config writer
     *
     * @return Config\WriterHelper
     */
    protected function getWriter()
    {
        if ($this->writer === null) {
            $this->writer = $this->getHelperSet()->get(Config\WriterHelper::NAME);
        }

        return $this->writer;
    }

    /**
     * Set writer
     *
     * @param Config\WriterHelper $writer
     * @return $this
     */
    public function setWriter(Config\WriterHelper $writer)
    {
        $this->writer = $writer;

        return $this;
    }

    /**
     * Get XML merger
     *
     * @return \PreCommit\XmlMerger
     */
    protected function getXmlMerger()
    {
        return ConfigInstance::getXmlMerger();
    }

    /**
     * Get XML object with value
     *
     * @param string $xpath
     * @param string $value
     * @return \SimpleXMLElement
     */
    protected function getXmlUpdate($xpath, $value)
    {
        $nodes    = explode('/', $xpath);
        $startXml = '';
        $endXml   = '';
        $last     = count($nodes) - 1;
        foreach ($nodes as $level => $node) {
            if ($last === $level) {
                $startXml .= "<$node>$value</$node>\n";
            } else {
                $startXml .= "<$node>\n";
                $endXml = "</$node>\n".$endXml;
            }
        }
        $startXml = rtrim($startXml);
        $endXml   = rtrim($endXml);

        //@startSkipCommitHooks
        $xml
            = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<config>
{$startXml}
{$endXml}
</config>
XML;

        //@finishSkipCommitHooks

        return simplexml_load_string($xml);
    }

    /**
     * Get cached config files
     *
     * @return array
     * @throws \PreCommit\Exception
     */
    protected function getCachedConfigFiles()
    {
        $finder = new Finder();
        $list   = array();
        foreach ($finder->files()->in(ConfigInstance::getCacheDir())->name('*.xml') as $file) {
            $list[] = $file->getRealpath();
        }

        return $list;
    }

    /**
     * Write empty XML file
     *
     * @param string $file
     * @throws \PreCommit\Command\Exception
     */
    protected function writeEmptyXmlFile($file)
    {
        //@startSkipCommitHooks
        $xml
            = <<<XML
<?xml version="1.0" encoding="UTF-8"?><config />
XML;
        //@finishSkipCommitHooks

        $this->getWriter()->writeContent($file, $xml);
    }
}
