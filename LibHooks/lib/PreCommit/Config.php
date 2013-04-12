<?php
namespace PreCommit;

/**
 * Class for get config
 */
class Config extends \SimpleXMLElement
{
    /**
     * @var Config
     */
    static protected $_instance;

    /**
     * Get config instance
     *
     * @param array $options
     * @return Config
     */
    static public function getInstance(array $options = array())
    {
        if (!self::$_instance) {
            self::$_instance = simplexml_load_file($options['file'], '\\PreCommit\\Config');
        }
        return self::$_instance;
    }

    /**
     * Get node by xpath
     *
     * @param string $xpath
     * @param bool $isArray
     * @return array|Config
     */
    public function getNode($xpath, $isArray = false)
    {
        $result = self::$_instance->xpath('/config/' . $xpath);
        if ($isArray) {
            return $result;
        } else {
            return $result[0];
        }

    }
}

