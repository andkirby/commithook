<?php

namespace PreCommit\Composer\Command\Helper;

use PreCommit\Config as ConfigInstance;
use Symfony\Component\Console\Helper\Helper;

/**
 * Helper for writing config
 *
 * @package PreCommit\Composer\Command\Helper
 */
class Config extends Helper
{
    /**
     * Helper name
     */
    const NAME = 'commithook_config';

    /**
     * Value to write
     *
     * Format: xpath => value
     *
     * @var array
     */
    protected $_values = array();

    /**
     * @inheritDoc
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
        $this->_values = array();
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
        $this->_values[$xpath] = $value;
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
        $config  = $this->loadConfig($configFile);

        $updated = false;
        foreach ($this->_values as $xpath => $value) {
            if ($config->getNode($xpath) == $value) {
                continue;
            }
            $this->setValueToXml($config, $xpath, $value);
            $updated = true;
        }

        if ($updated) {
            $this->getWriter()->write(
                $config, $configFile
            );
        }
        return $updated;
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
            $this->getWriter()->writeContent(
                $file, '<?xml version="1.0"?><config></config>'
            );
        }
        return ConfigInstance::loadInstance(array('file' => $file), false);
    }

    /**
     * Get config writer
     *
     * @return Config\Writer
     */
    protected function getWriter()
    {
        return $this->getHelperSet()->get(Config\Writer::NAME);
    }

    /**
     * Save value to XML
     *
     * @param ConfigInstance $config
     * @param string         $xpath
     * @param string         $value
     * @return $this
     */
    protected function setValueToXml(ConfigInstance $config, $xpath, $value)
    {
        $this->getXmlMerger()->merge(
            $config,
            $this->getXmlUpdate($xpath, $value)
        );
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
                $endXml .= "</$node>\n";
            }
        }
        $startXml = rtrim($startXml);
        $endXml   = rtrim($endXml);

        $xml
            = <<<XML
<?xml version="1.0"?>
<config>
{$startXml}
{$endXml}
</config>
XML;
        return simplexml_load_string($xml);
    }

    /**
     * Write configuration value by XML path
     *
     * @param string $configFile
     * @param string $xpath
     * @param string $value
     * @return bool
     * @throws \PreCommit\Composer\Exception
     */
    public function writeValue($configFile, $xpath, $value)
    {
        $config = $this->loadConfig($configFile);
        if ($config->getNode($xpath) == $value) {
            return false;
        }

        $this->setValueToXml($config, $xpath, $value);

        $this->getWriter()->write(
            $config, $configFile
        );
        return true;
    }
}
