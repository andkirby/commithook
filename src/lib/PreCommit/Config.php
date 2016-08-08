<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
namespace PreCommit;

use PreCommit\Command\Command\Helper\Config as ConfigHelper;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

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
    protected static $instance;

    /**
     * Project directory
     *
     * @var string
     */
    protected static $rootDir;

    /**
     * CommitHook root directory
     *
     * @var string
     */
    protected static $projectDir;

    /**
     * CommitHook config files
     *
     * @var array
     */
    protected static $configFiles = array();

    /**
     * Get config cache file
     *
     * @return string
     */
    public static function getCacheFile()
    {
        return self::getCacheDir().DIRECTORY_SEPARATOR.
        md5(self::getInstance()->getNode('version').static::getProjectDir())
        .'.xml';
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
        $dir  = self::readPath($path);
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0750, true)) {
                throw new Exception(
                    "Unable to create cache directory by path '$dir'"
                );
            }
        }

        return realpath($dir);
    }

    /**
     * Init config instance
     *
     * @param array $options
     *
     * @return $this
     * @throws Exception
     */
    public static function initInstance(array $options = array())
    {
        if (!self::$instance || !empty($options['file'])) {
            if (null === self::$instance) {
                /**
                 * Init empty XML object
                 */
                /** @var Config $config */
                self::$instance = simplexml_load_string(
                    '<?xml version="1.0" encoding="UTF-8"?><config />',
                    '\\PreCommit\\Config'
                );
            }

            self::$instance = self::getXmlMerger()->merge(self::$instance, self::loadInstance($options));

            if (empty($options['root_dir'])) {
                self::setSrcRootDir(realpath(__DIR__.'/../../'));
            }
            if (!empty($options['project_dir'])) {
                self::setProjectDir($options['project_dir']);
            }
            if (self::getProjectDir() && self::$rootDir && !self::loadCache()) {
                self::mergeExtraConfig();
            }
        }

        return self::$instance;
    }

    /**
     * Get config instance
     *
     * @return $this
     * @throws Exception
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            throw new Exception('Please use Config::initInstance().');
        }

        return self::$instance;
    }

    /**
     * Load config instance
     *
     * @param array $options
     * @param bool  $setFile
     *
     * @return $this
     * @throws \PreCommit\Exception
     */
    public static function loadInstance(array $options, $setFile = true)
    {
        $file = $options['file'];
        if (!isset($file)) {
            throw new Exception('Options parameter "file" is required.');
        }
        if (!file_exists($file)) {
            $file = $file.'.dist';
        }
        if (!file_exists($file)) {
            throw new Exception("File '{$options['file']}' not found.");
        }
        /** @var Config $config */
        $config = simplexml_load_file($file, '\\PreCommit\\Config');

        if ($setFile) {
            self::setConfigFile('root', $file);
        }

        return $config;
    }

    /**
     * Get config file
     *
     * @param string $name
     * @param string $file
     *
     * @return null|string
     */
    public static function setConfigFile($name, $file)
    {
        //save file to config
        $writer = new ConfigHelper();
        $writer->setValueToXml(self::$instance, 'cache/config_files/'.$name, $file);

        return self::$configFiles[$name] = $file;
    }

    /**
     * Set project dir by hook file
     *
     * @todo  It should be removed from Config
     *
     * @param string $dir
     *
     * @return string
     */
    public static function setSrcRootDir($dir)
    {
        static::$rootDir = rtrim($dir, '\\/');
    }

    /**
     * Get project dir
     *
     * @todo It should be removed from Config
     * @return string
     */
    public static function getProjectDir()
    {
        return static::$projectDir;
    }

    /**
     * Set project dir by hook file
     *
     * @todo It should be removed from Config
     *
     * @param string $dir
     *
     * @return string
     */
    public static function setProjectDir($dir)
    {
        static::$projectDir = $dir;
    }

    /**
     * Load cached config
     *
     * @return bool             Returns FALSE in case it couldn't load cached
     *                          config
     */
    public static function loadCache()
    {
        //load config from cache
        $configCacheFile = self::getCacheFile();
        if (!self::isCacheDisabled() && is_file($configCacheFile)) {
            $configCached = self::loadInstance(
                array('file' => $configCacheFile)
            );
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
     * Check cache enabling
     *
     * @return null|string
     */
    public static function isCacheDisabled()
    {
        return (bool) self::getInstance()->getNode('disable_cache');
    }

    /**
     * Replace instance
     *
     * @param Config $config
     */
    public static function replaceInstance(Config $config)
    {
        self::$instance = $config;
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
        self::mergeFiles(
            $merger,
            array(
                'user-root'    => 'HOME/.commithook/user-root.xml',
                'project-root' => 'PROJECT_DIR/.commithook/root.xml',
            ),
            $allowed
        );

        /**
         * Merge configuration files
         */
        self::mergeFiles(
            $merger,
            self::getInstance()->getNodeArray('additional_config'),
            $allowed
        );

        //write cached config file
        $cacheFile = self::getCacheFile();
        if (!self::isCacheDisabled()
            && is_writeable(
                pathinfo($cacheFile, PATHINFO_DIRNAME)
            )
        ) {
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
        $merger->addCollectionNode(
            'validators/FileFilter/filter/skip/files/file'
        );
        $merger->addCollectionNode(
            'validators/FileFilter/filter/skip/paths/path'
        );
        $merger->addCollectionNode(
            'validators/FileFilter/filter/protect/files/file'
        );
        $merger->addCollectionNode(
            'validators/FileFilter/filter/protect/paths/path'
        );

        return $merger;
    }

    /**
     * Read path
     *
     * @param string $path
     *
     * @return string
     * @throws Exception
     */
    public static function readPath($path)
    {
        $updated = false;
        if (0 === strpos($path, 'PROJECT_DIR')) {
            $updated = true;
            $path    = str_replace(
                'PROJECT_DIR',
                static::getProjectDir(),
                $path
            );
        }
        if (false !== strpos($path, 'PROJECT_NAME')) {
            $updated = true;
            $path    = str_replace(
                'PROJECT_NAME',
                basename(static::getProjectDir()),
                $path
            );
        }
        if (0 === strpos($path, 'HOME')) {
            $updated = true;
            $path    = str_replace('HOME', self::getHomeUserDir(), $path);
        }
        if (!$updated) {
            $path = static::$rootDir.DIRECTORY_SEPARATOR.$path;
        }

        return $path;
    }

    /**
     * Get config file
     *
     * @param string $name
     *
     * @return null|string
     */
    public static function getConfigFile($name)
    {
        if (!isset(self::$configFiles[$name])) {
            //try to load config files list from config
            self::$configFiles[$name] = self::getInstance()->getNode('cache/config_files/'.$name);
        }
        return self::$configFiles[$name];
    }

    /**
     * Get config file
     *
     * @return null|string
     */
    public static function getConfigFiles()
    {
        return self::$configFiles;
    }

    /**
     * Merge files
     *
     * @param XmlMerger   $merger
     * @param array       $paths
     * @param array       $allowed
     * @param Config|null $targetConfig
     */
    protected static function mergeFiles(
        $merger,
        $paths,
        array $allowed = null,
        $targetConfig = null
    ) {
        foreach ($paths as $key => $path) {
            if ($allowed && !in_array($key, $allowed)) {
                continue;
            }
            $path = self::readPath($path);

            if ('.xml' !== substr($path, -4)) {
                /**
                 * It's a directory with possible XML files
                 */
                $files = array();
                foreach (self::findConfigFilesInPath($path) as $file) {
                    $files[] = $file->getRealPath();
                }
            } else {
                self::setConfigFile($key, $path);
                $fs = new Filesystem();
                if (!$fs->exists($path)) {
                    continue;
                }
                $files = array($path);
            }

            $targetConfig = $targetConfig ?: self::getInstance();
            foreach ($files as $file) {
                $merger->merge(
                    $targetConfig,
                    self::loadXmlFileToMerge($file)
                );
            }
        }
    }

    /**
     * Get home user directory
     *
     * @return string
     * @throws Exception
     */
    protected static function getHomeUserDir()
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
     *
     * @return self
     * @throws \PreCommit\Exception
     */
    protected static function loadXmlFileToMerge($file)
    {
        try {
            return simplexml_load_file($file, __CLASS__);
        } catch (\Exception $e) {
            throw new Exception("Cannot load XML file '$file'");
        }
    }

    /**
     * Find config files in path
     *
     * It will ignore root.xml file
     *
     * @param string $path
     * @return SplFileInfo[]
     */
    protected static function findConfigFilesInPath($path)
    {
        $fs = new Filesystem();
        if (!$fs->exists($path)) {
            return array();
        }

        $finder = new Finder();

        return $finder->files()->name('*.xml')->notName('root.xml')->in($path);
    }

    /**
     * Get node/s by expression
     *
     * @param string $xpath
     *
     * @return array|null|string
     * @throws Exception
     */
    public function getNodesExpr($xpath)
    {
        $result = $this->xpath($xpath);
        if (is_array($result)) {
            //TODO looks like it's always an array
            $data = array();
            foreach ($result as $node) {
                /** @var Config $node */
                $data[$node->getName()] = $this->getNodeValue($node);
            }

            return $data;
        } elseif ($result instanceof Config) {
            return $this->getNodeValue($result);
        }

        return null;
    }

    /**
     * Catch exception and show failed XPath
     *
     * @param string $path
     *
     * @return \SimpleXMLElement[]
     * @throws \PreCommit\Exception
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

        $result = isset($result[$last]) ? (array)$result[$last] : array();

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
        $result = $this->xpath(self::XPATH_START.$xpath);
        $result = isset($result[0]) ? (array)$result[0] : array();
        $result = json_decode(json_encode($result), true);

        //remove XML comments (hack) TODO investigate a problem
        unset($result['comment']);

        return $result;
    }

    /**
     * Get node by xpath
     *
     * @param string $xpath
     *
     * @return string|null
     */
    public function getNode($xpath)
    {
        $result = $this->xpath(self::XPATH_START.$xpath);
        $result = isset($result[0]) ? (string)$result[0] : null;

        return $result;
    }

    /**
     * Get node value
     *
     * @param Config $node
     *
     * @return string|array
     */
    protected function getNodeValue($node)
    {
        if ($node->count()) {
            $data = array();
            /** @var Config $child */
            foreach ($node->children() as $child) {
                $data[$child->getName()] = $this->getNodeValue($child);
            }

            return $data;
        } else {
            return (string)$node;
        }
    }
}
