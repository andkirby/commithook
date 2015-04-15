<?php
namespace PreCommit;

use PreCommit\Exception;

/**
 * Class for get config
 */
class Config extends \SimpleXMLElement
{
    /**
     * Root node XPATH
     */
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
     * @param string $projectDir
     * @return string
     */
    public static function getCacheFile($rootPath, $projectDir)
    {
        return self::getCacheDir($rootPath)
            . DIRECTORY_SEPARATOR
            . md5(self::getInstance()->getNode('version') . $projectDir)
            . '.xml';
    }

    /**
     * Load cached config
     *
     * @param string $rootPath
     * @param string $projectDir
     * @return bool             Returns FALSE in case it couldn't load cached config
     */
    public static function loadCache($rootPath, $projectDir)
    {
        //load config from cache
        $configCacheFile = self::getCacheFile($rootPath, $projectDir);
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
     * @param string $projectDir
     * @return void
     */
    public static function mergeExtraConfig($rootPath, $projectDir)
    {
        $merger = new XmlMerger();
        $merger->addCollectionNode('validators/FileFilter/filter/skip/files/file');
        $merger->addCollectionNode('validators/FileFilter/filter/skip/paths/path');
        $merger->addCollectionNode('validators/FileFilter/filter/protect/files/file');
        $merger->addCollectionNode('validators/FileFilter/filter/protect/paths/path');
        foreach (self::getInstance()->getNodeArray('additional_config') as $file) {
            if (0 === strpos($file, 'PROJECT_DIR')) {
                $file = str_replace('PROJECT_DIR', $projectDir, $file);
            } elseif (0 === strpos($file, 'HOME')) {
                $file = str_replace('HOME', self::_getHomeUserDir(), $file);
            } else {
                $file = $rootPath . DIRECTORY_SEPARATOR . $file;
            }
            if (!is_file($file)) {
                continue;
            }
            try {
                $xml = self::loadXmlFileToMerge($file);
                $merger->merge(self::getInstance(), $xml);
            } catch (\Exception $e) {
                echo 'XML ERROR: Could not load additional config file ' . $file;
                echo PHP_EOL;
            }
        }

        //write cached config file
        $cacheFile = self::getCacheFile($rootPath, $projectDir);
        if (is_writeable(pathinfo($cacheFile, PATHINFO_DIRNAME))) {
            self::getInstance()->asXML($cacheFile);
        }
    }

    /**
     * Get home user directory
     *
     * @return string
     * @throws \PreCommit\Exception
     */
    protected static function _getHomeUserDir()
    {
        if (isset($_SERVER['USERPROFILE'])) {
            $home = $_SERVER['USERPROFILE'];
        } elseif (isset($_SERVER['HOME'])) {
            $home = $_SERVER['HOME'];
        } else {
            throw new Exception('Path to user home directory not found.');
        }
        return $home;
    }

    /**
     * Load file to merge
     *
     * @param string $file
     * @return \SimpleXMLElement
     */
    protected static function loadXmlFileToMerge($file)
    {
        return simplexml_load_file($file);
    }

    /**
     * Get project dir from hook file
     *
     * @todo It should be removed from Config
     * @param string $hookFile
     * @return string
     */
    public static function getProjectDir($hookFile)
    {
        return $projectDir = realpath(pathinfo($hookFile, PATHINFO_DIRNAME) . '/../..');
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
     * @param string $rootPath
     * @return string
     */
    public static function getCacheDir($rootPath)
    {
        return realpath(
            $rootPath . DIRECTORY_SEPARATOR
                . trim(Config::getInstance()->getNode('cache_dir'), '\\/')
        );
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

