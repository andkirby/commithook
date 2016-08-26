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
    protected $values = [];

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
        $this->values = [];

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
        if (null === $value) {
            $nodeValue = $config->xpath($xpath);
            /** @var ConfigInstance $nodeValue */
            $nodeValue = $nodeValue ? $nodeValue[0] : null;
            if ($nodeValue) {
                $this->removeNode($config, $xpath);
            }
        } else {
            $this->getXmlMerger()->merge(
                $config,
                $this->getXmlUpdate($xpath, $value)
            );
        }

        return $this;
    }

    /**
     * Write configuration value by XML path
     *
     * @param string $configFile
     * @param string $xpath
     * @param string $value
     * @return bool
     * @throws \PreCommit\Console\Exception
     */
    public function writeValue($configFile, $xpath, $value)
    {
        $config = $this->loadConfig($configFile);
        if ((string) $value === $config->getNode($xpath)) {
            return false;
        }

        $this->setValueToXml($config, $xpath, ($value === null ? null : (string) $value));

        $this->getWriter()->write(
            $config,
            $configFile
        );
        $this->clearCache();

        return true;
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
     * Remove empty nodes
     *
     * @param string              $xpath
     * @param ConfigInstance      $config
     * @param ConfigInstance|null $parent
     * @return $this
     */
    public function removeEmptyXpath($xpath, $config, $parent = null)
    {
        $names = explode('/', $xpath);
        $this->removeIfNodeEmpty($names, $config, $parent);

        return $this;
    }

    /**
     * Remove if node is empty
     *
     * @see ConfigHelper::removeEmptyXpath()
     * @param array               $names  Node names list in sequence from XPath
     * @param ConfigInstance      $node
     * @param ConfigInstance|null $parent
     * @return $this
     */
    public function removeIfNodeEmpty($names, $node, $parent = null)
    {
        if ($node && $node->children()) {
            $parentParent = $parent;
            $parent       = $node;
            /** @var ConfigInstance $node */
            $node = $node->{current($names)};
            if (!next($names)) {
                return $this;
            }

            $this->removeIfNodeEmpty($names, $node, $parent);

            if ($parentParent) {
                //try to remove original parent
                $this->removeIfNodeEmpty($names, $parent, $parentParent);
            }
        } elseif ($node && $parent && !$node->hasComment()) {
            unset($parent->{$node->getName()});
        }

        return $this;
    }

    /**
     * Remove node
     *
     * @param ConfigInstance $config
     * @param string         $xpath
     * @return $this
     */
    public function removeNode(ConfigInstance $config, $xpath)
    {
        $names = explode('/', $xpath);
        $max   = count($names);

        $configNode = $config;
        foreach ($names as $i => $name) {
            if ($i !== $max - 1) {
                $configNode = $configNode->{$name};
            } else {
                unset($configNode->{$name});
            }
        }

        $this->removeEmptyXpath($xpath, $config);

        return $this;
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

        return ConfigInstance::loadInstance(['file' => $file], false);
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
        $list   = [];
        foreach ($finder->files()->in(ConfigInstance::getCacheDir())->name('*.xml') as $file) {
            $list[] = $file->getRealpath();
        }

        return $list;
    }

    /**
     * Write empty XML file
     *
     * @param string $file
     * @throws \PreCommit\Console\Exception
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
