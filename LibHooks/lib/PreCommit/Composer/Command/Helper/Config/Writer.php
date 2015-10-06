<?php
namespace PreCommit\Composer\Command\Helper\Config;

use PreCommit\Composer\Exception;
use PreCommit\Config;
use Symfony\Component\Console\Helper\Helper;

/**
 * Helper for writing config
 *
 * @package PreCommit\Composer\Command\Helper
 */
class Writer extends Helper
{
    /**
     * Helper name
     */
    const NAME = 'commithook_config_writer';

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * Write configuration value by XML path
     *
     * @param Config $config
     * @param string $file
     * @return bool
     * @throws Exception
     */
    public function write($config, $file)
    {
        $this->writeContent($file, $this->getWellFormattedXml($config));
        return true;
    }

    /**
     * Write content
     *
     * @param string $file
     * @param string $content
     * @return $this
     * @throws Exception
     */
    public function writeContent($file, $content)
    {
        if (!file_put_contents($file, $content)) {
            throw new Exception("Cannot write file '$file'.");
        }
        return $this;
    }

    /**
     * Format XML
     *
     * @param Config $config
     * @return string
     */
    protected function getWellFormattedXml($config)
    {
        //use DomDocument to make well-formatted XML
        $doc = new \DomDocument('1.0', 'utf-8');

        $doc->preserveWhiteSpace = false;
        $doc->formatOutput       = true;
        $doc->loadXML($config->asXML());
        return str_replace('  ', '    ', $doc->saveXML()); //use 4 spaces as indent
    }

    /**
     * Load config
     *
     * @param string $file
     * @return Config
     * @throws \PreCommit\Exception
     */
    protected function loadConfig($file)
    {
        if (!file_exists($file)) {
            $xml
                = <<<XML
<?xml version="1.0"?>
<config>
</config>
XML;
            $this->writeContent($file, $xml);
        }
        $config = Config::loadInstance(array('file' => $file), false);
        return $config;
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
        $total    = count($nodes);
        foreach ($nodes as $level => $node) {
            $level += 1; //set real level because start from zero
            if ($total === $level) {
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
}
