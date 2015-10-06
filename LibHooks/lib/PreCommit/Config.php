<?php
namespace PreCommit;

use PreCommit\Jira\Api\Exception;

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
     * CommitHook config files
     *
     * @var array
     */
    protected $_configFiles = array();

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
            self::$_instance = self::loadInstance($options);
            if (empty($options['root_dir'])) {
                self::setSrcRootDir(realpath(__DIR__ . '/../../'));
            }
            if (!empty($options['project_dir'])) {
                self::setProjectDir($options['project_dir']);
            }
            if (self::getProjectDir() && self::$_rootDir && !self::loadCache()) {
                self::mergeExtraConfig();
            }
        }
        return self::$_instance;
    }

    /**
     * Load config instance
     *
     * @param array $options
     * @return $this
     * @throws \PreCommit\Exception
     * @throws \PreCommit\Jira\Api\Exception
     */
    public static function loadInstance(array $options)
    {
        if (!isset($options['file'])) {
            throw new Exception('Options parameter "file" is required.');
        }
        if (!file_exists($options['file'])) {
            $options['file'] = $options['file'] . '.dist';
        }
        if (!file_exists($options['file'])) {
            throw new \PreCommit\Exception("File '{$options['file']}' not found.");
        }
        /** @var Config $config */
        $config = simplexml_load_file($options['file'], '\\PreCommit\\Config');

        $config->_configFiles['root'] = $options['file'];
        return $config;
    }

    /**
     * Get config cache file
     *
     * @return string
     */
    public static function getCacheFile()
    {
        return self::getCacheDir()
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
        $merger = self::getXmlMerger();

        /**
         * Try to get user root file
         */
        self::_mergeFiles($merger, array('user-root' => 'HOME/.commithook/user-root.xml'), $allowed);

        /**
         * Merge configuration files
         */
        self::_mergeFiles(
            $merger,
            self::getInstance()->getNodeArray('additional_config'),
            $allowed
        );

        //write cached config file
        $cacheFile = self::getCacheFile();
        if (!self::isCacheDisabled() && is_writeable(pathinfo($cacheFile, PATHINFO_DIRNAME))) {
            self::getInstance()->asXML($cacheFile);
        }
    }

    /**
     * Get XML merger
     *
     * @return \PreCommit\XmlMerger
     */
    public static function getXmlMerger()
    {
        $merger = new XmlMerger();
        $merger->addCollectionNode('validators/FileFilter/filter/skip/files/file');
        $merger->addCollectionNode('validators/FileFilter/filter/skip/paths/path');
        $merger->addCollectionNode('validators/FileFilter/filter/protect/files/file');
        $merger->addCollectionNode('validators/FileFilter/filter/protect/paths/path');
        return $merger;
    }

    /**
     * Merge files
     *
     * @param XmlMerger   $merger
     * @param array       $files
     * @param array       $allowed
     * @param Config|null $targetConfig
     */
    protected static function _mergeFiles($merger, $files, array $allowed = null, $targetConfig = null)
    {
        foreach ($files as $key => $file) {
            if ($allowed && !in_array($key, $allowed)) {
                continue;
            }
            $file = self::_readPath($file);
            if (!is_file($file)) {
                continue;
            }
            $targetConfig = $targetConfig ?: self::getInstance();
            $xml          = self::_loadXmlFileToMerge($file);
            $merger->merge($targetConfig, $xml);
            $targetConfig->setConfigFile($key, $file);
        }
    }

    /**
     * Get config file
     *
     * @param string $name
     * @return null|string
     */
    public function getConfigFile($name)
    {
        return isset($this->_configFiles[$name]) ? $this->_configFiles[$name] : null;
    }

    /**
     * Get config file
     *
     * @return null|string
     */
    public function getConfigFiles()
    {
        return $this->_configFiles;
    }

    /**
     * Get config file
     *
     * @param string $name
     * @param string $file
     * @return null|string
     */
    public function setConfigFile($name, $file)
    {
        return $this->_configFiles[$name] = $file;
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
     * @param string $dir
     * @return string
     */
    public static function setProjectDir($dir)
    {
        static::$_projectDir = $dir;
    }

    /**
     * Set project dir by hook file
     *
     * @todo  It should be removed from Config
     * @param string $dir
     * @return string
     */
    public static function setSrcRootDir($dir)
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
     * Catch exception and show failed XPath
     *
     * @param string $path
     * @return \SimpleXMLElement[]
     * @throws \PreCommit\Jira\Api\Exception
     */
    public function xpath($path)
    {
        try {
            return parent::xpath($path);
        } catch (\Exception $e) {
            throw new Exception("Invalid XPath '$path'");
        }
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
        $dir  = self::_readPath($path);
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
        $updated = false;
        if (0 === strpos($path, 'PROJECT_DIR')) {
            $updated = true;
            $path    = str_replace('PROJECT_DIR', static::getProjectDir(), $path);
        }
        if (0 === strpos($path, 'PROJECT_NAME')) {
            $updated = true;
            $path    = str_replace('PROJECT_NAME', basename(static::getProjectDir()), $path);
        }
        if (0 === strpos($path, 'HOME')) {
            $updated = true;
            $path    = str_replace('HOME', self::_getHomeUserDir(), $path);
        }
        if (!$updated) {
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
        $last  = null;
        $arr   = explode('/', $xpath);
        $last  = array_pop($arr);
        $xpath = implode('/', $arr);

        $result = $this->getNodeArray($xpath);

        $result = isset($result[$last]) ? (array)$result[$last] : array();
        return $result;
    }
}

