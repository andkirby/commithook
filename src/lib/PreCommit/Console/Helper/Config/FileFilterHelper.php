<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
namespace PreCommit\Console\Helper\Config;

use PreCommit\Console\Exception;
use Symfony\Component\Console\Helper\Helper;

/**
 * Helper for writing config
 *
 * @package PreCommit\Console\Helper
 */
class FileFilterHelper extends Helper
{
    /**
     * Helper name
     */
    const NAME = 'commithook_config_file';

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * Get converted path as XML node name
     *
     * @param string $path
     * @return null|string
     * @throws Exception
     */
    public function path2XmlNode($path)
    {
        /**
         * Element names cannot start with the letters xml (or XML, or Xml, etc)
         * Element names must start with a letter or underscore
         *
         * @link http://www.w3schools.com/xml/xml_elements.asp
         */
        $path = preg_replace('#^([^A-z_]|xml)#i', '_$1', $this->normalizePath($path));

        /**
         * Element names can contain letters, digits, hyphens, underscores, and periods
         */
        return preg_replace('#[^A-z0-9_.-]#', '_', $path);
    }

    /**
     * Filter path
     *
     * @param string $path
     * @param bool   $asDir
     * @param bool   $useRoot Allow to use root value,
     *                        if FALSE and $asDir = FALSE it will be converted into empty value
     * @return string
     */
    public function normalizePath($path, $asDir = false, $useRoot = true)
    {
        $path = str_replace('\\', '/', trim($path));
        if ($useRoot && $path === '/') {
            return $path;
        }

        $path = rtrim($path, '/');

        if (true === $asDir) {
            $path .= '/';
        }

        return $path;
    }
}
