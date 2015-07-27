<?php
namespace PreCommit;

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
     * Project directory
     *
     * @var string
     */
    protected static $_rootDir;

    /**
     * CommitHook root directory
     *
     * @var string
     */
    protected static $_projectDir;

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
     * @return string
     */
    public static function getCacheFile()
    {
        return self::getCacheDir(static::$_rootDir)
            . DIRECTORY_SEPARATOR
            . md5(self::getInstance()->getNode('version') . static::getProjectDir())
            . '.xml';
    }

    /**
     * Check cache enabling
     *
     * @return null|string
     */
    public static function isCacheDisabled()
    {
        return (bool)self::getInstance()->getNode('disable_cache');
    }

    /**
     * Load cached config
     *
     * @return bool             Returns FALSE in case it couldn't load cached config
     */
    public static function loadCache()
    {
        //load config from cache
        $configCacheFile = self::getCacheFile();
        if (!self::isCacheDisabled() && is_file($configCacheFile)) {
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
     * @param array $allowed
     */
    public static function mergeExtraConfig(array $allowed = null)
    {
        $merger = new XmlMerger();
        $merger->addCollectionNode('validators/FileFilter/filter/skip/files/file');
        $merger->addCollectionNode('validators/FileFilter/filter/skip/paths/path');
        $merger->addCollectionNode('validators/FileFilter/filter/protect/files/file');
        $merger->addCollectionNode('validators/FileFilter/filter/protect/paths/path');
        $files = self::getInstance()->getNodeArray('additional_config');

        self::_mergeFiles($merger, array('HOME/user-root.xml'), $allowed);
        self::_mergeFiles($merger, $files, $allowed);

        //write cached config file
        $cacheFile = self::getCacheFile();
        if (!self::isCacheDisabled() && is_writeable(pathinfo($cacheFile, PATHINFO_DIRNAME))) {
            self::getInstance()->asXML($cacheFile);
        }
    }

    /**
     * Merge files
     *
     * @param XmlMerger $merger
     * @param array     $files
     * @param array     $allowed
     */
    protected static function _mergeFiles($merger, $files, array $allowed = null)
    {
        foreach ($files as $key => $file) {
            if ($allowed && !in_array($key, $allowed)) {
                continue;
            }
            $file = self::_readPath($file);
            if (!is_file($file)) {
                continue;
            }
            $xml = self::_loadXmlFileToMerge($file);
            $merger->merge(self::getInstance(), $xml);
        }
    }

    /**
     * Get home user directory
     *
     * @return string
     * @throws Exception
     */
    protected static function _getHomeUserDir()
    {
        //@startSkipCommitHooks
        if (isset($_SERVER['USERPROFILE'])) {
            $home = $_SERVER['USERPROFILE'];
        } elseif (isset($_SERVER['HOME'])) {
            $home = $_SERVER['HOME'];
        } else {
            throw new Exception('Path to user home directory not found.');
        }
        //@finishSkipCommitHooks
        return $home;
    }

    /**
     * Load file to merge
     *
     * @param string $file
     * @return \SimpleXMLElement
     */
    protected static function _loadXmlFileToMerge($file)
    {
        return simplexml_load_file($file);
    }

    /**
     * Get project dir
     *
     * @todo It should be removed from Config
     * @return string
     */
    public static function getProjectDir()
    {
        return static::$_projectDir;
    }

    /**
     * Set project dir by hook file
     *
     * @todo It should be removed from Config
     * @param string $hookFile
     * @return string
     */
    public static function setProjectDir($hookFile)
    {
        static::$_projectDir = realpath(pathinfo($hookFile, PATHINFO_DIRNAME) . '/../..');
    }

    /**
     * Set project dir by hook file
     *
     * @todo  It should be removed from Config
     * @param string $dir
     * @return string
     */
    public static function setRootDir($dir)
    {
        static::$_rootDir = rtrim($dir, '\\/');
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

        //remove XML comments (hack) TODO investigate a problem
        unset($result['comment']);
        return $result;
    }

    /**
     * Get cache directory
     *
     * @return string
     * @throws Exception
     */
    public static function getCacheDir()
    {
        $path = trim(Config::getInstance()->getNode('cache_dir'), '\\/');
        $dir = self::_readPath($path);
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0750, true)) {
                throw new Exception("Unable to create cache directory by path '$dir'");
            }
        }
        return realpath($dir);
    }

    /**
     * Read path
     *
     * @param string $path
     * @return string
     * @throws Exception
     */
    protected static function _readPath($path)
    {
        if (0 === strpos($path, 'PROJECT_DIR')) {
            $path = str_replace('PROJECT_DIR', static::getProjectDir(), $path);
        } elseif (0 === strpos($path, 'HOME')) {
            $path = str_replace('HOME', self::_getHomeUserDir(), $path);
        } else {
            $path = static::$_rootDir . DIRECTORY_SEPARATOR . $path;
        }
        return $path;
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

