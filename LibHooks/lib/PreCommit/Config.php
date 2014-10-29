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
        if (!self::$_instance || !empty($options['file'])) {
            $config = self::loadInstance($options);
            self::$_instance = $config;
        }
        return self::$_instance;
    }

    /**
     * Load config instance
     *
     * @param array $options
     * @return $this
     * @throws Exception
     */
    public static function loadInstance(array $options)
    {
        if (!isset($options['file'])) {
            throw new Exception('Options parameter "file" is required.');
        }
        if (!file_exists($options['file'])) {
            $options['file'] = $options['file'] . '.dist';
        }
        return simplexml_load_file($options['file'], '\\PreCommit\\Config');
    }

    /**
     * Get config cache file
     *
     * @param string $rootPath
     * @param string $hookFile
     * @return string
     */
    public static function getCacheFile($rootPath, $hookFile)
    {
        return $rootPath . DIRECTORY_SEPARATOR . Config::getInstance()->getNode('cache_dir')
            . DIRECTORY_SEPARATOR
            . md5(self::getInstance()->getNode('version') . pathinfo($hookFile, PATHINFO_DIRNAME)) . '.xml';
    }

    /**
     * Load cached config
     *
     * @param string $rootPath
     * @param string $hookFile
     * @return bool             Returns FALSE in case it couldn't load cached config
     */
    public static function loadCache($rootPath, $hookFile)
    {
        //load config from cache
        $configCacheFile = self::getCacheFile($rootPath, $hookFile);
        if (is_file($configCacheFile)) {
            $configCached = self::loadInstance(array('file' => $configCacheFile));
            if (version_compare(
                $configCached->getNode('version'),
                self::getInstance()->getNode('version'),
                '='
            )) {
                self::replaceInstance($configCached);
                return true;
            }
        }
        return false;
    }

    /**
     * Merge additional config files
     *
     * @param string $rootPath
     * @param string $hookFile
     * @return void
     */
    public static function mergeExtraConfig($rootPath, $hookFile)
    {
        /**
         * Merge extra config files
         */
        $projectDir = realpath(pathinfo($hookFile, PATHINFO_DIRNAME) . '/../..');

        $merger = new XmlMerger();
        $merger->addCollectionNode('validators/FileFilter/filter/skip/files/file');
        $merger->addCollectionNode('validators/FileFilter/filter/skip/paths/path');
        $merger->addCollectionNode('validators/FileFilter/filter/protect/files/file');
        $merger->addCollectionNode('validators/FileFilter/filter/protect/paths/path');
        foreach (self::getInstance()->getNodeArray('additional_config') as $file) {
            if (0 === strpos($file, 'PROJECT_DIR')) {
                $file = str_replace('PROJECT_DIR', $projectDir, $file);
            } else {
                $file = $rootPath . DIRECTORY_SEPARATOR . $file;
            }
            if (!is_file($file)) {
                continue;
            }
            try {
                $xml = simplexml_load_file($file);
                $merger->merge(self::getInstance(), $xml);
            } catch (\Exception $e) {
                echo 'XML ERROR: Could not load additional config file ' . $file;
                echo PHP_EOL;
            }
        }

        //write cached config file
        $cacheFile = self::getCacheFile($rootPath, $hookFile);
        if (is_writeable(pathinfo($cacheFile, PATHINFO_DIRNAME))) {
            self::getInstance()->asXML($cacheFile);
        }
    }

    /**
     * Replace instance
     *
     * @param Config $config
     */
    public static function replaceInstance(Config $config)
    {
        self::$_instance = $config;
    }

    /**
     * Get node by xpath
     *
     * @param string $xpath
     * @return string|null
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

