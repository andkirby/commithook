<?php
namespace PreCommit;

/**
 * Class for get config
 */
class Config extends \SimpleXMLElement
{
    const XPATH_START = '/config/';

    /**
     * Self instance
     *
     * @var Config
     */
    static protected $_instance;

    /**
     * Get config instance
     *
     * @param array $options
     * @return Config
     * @throws Exception
     */
    static public function getInstance(array $options = array())
    {
        if (!self::$_instance) {
            if (!isset($options['file'])) {
                throw new Exception('Options parameter "file" is required.');
            }
            if (!file_exists($options['file'])) {
                $options['file'] = $options['file'] . '.dist';
            }
            self::$_instance = simplexml_load_file($options['file'], '\\PreCommit\\Config');
        }
        return self::$_instance;
    }

    /**
     * Get node by xpath
     *
     * @param string $xpath
     * @return array|Config
     */
    public function getNode($xpath)
    {
        $result = $this->xpath(self::XPATH_START . $xpath);
        $result = isset($result[0]) ? (string)$result[0] : null;
        return $result;
    }

    /**
     * Get node array values
     *
     * @param string $xpath
     * @return array|null
     */
    public function getNodeArray($xpath)
    {
        $result = $this->xpath(self::XPATH_START . $xpath);
        $result = isset($result[0]) ? (array)$result[0] : array();
        $result = json_decode(json_encode($result), true);
        return $result;
    }

    /**
     * Get node multi values by xpath
     *
     * Multi-values means nodes with the same name in the same place
     *
     * @param string $xpath
     * @return array|Config
     */
    public function getMultiNode($xpath)
    {
        $last = null;
        $arr = explode('/', $xpath);
        $last = array_pop($arr);
        $xpath = implode('/', $arr);

        $result = $this->getNodeArray($xpath);

        $result = isset($result[$last]) ? (array) $result[$last] : array();
        return $result;
    }
}

