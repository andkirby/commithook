<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */

namespace PreCommit\Command\Command\Helper;

use PreCommit\Config as ConfigInstance;
use PreCommit\Exception;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

/**
 * Helper for writing config
 *
 * @package PreCommit\Command\Command\Helper
 */
class ClearCacheHelper extends Helper
{
    /**
     * Helper name
     */
    const NAME = 'clear_cache';

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * Clear cache
     *
     * @param array $options
     * @return $this
     */
    public function clear(array $options = array())
    {
        if (!$options) {
            $options['config'] = true;
            $options['issues'] = true;
        }

        if (!empty($options['issues'])) {
            $this->clearIssueCache();
        }
        if (!empty($options['config'])) {
            $this->clearConfigCache();
        }

        return $this;
    }

    /**
     * Clear cache
     *
     * @return $this
     * @throws Exception
     */
    public function clearCache()
    {
        $list = $this->getCachedConfigFiles();
        if ($list) {
            $fs = new Filesystem();
            $fs->remove($list);
        }

        return $this;
    }

    /**
     * Clear cache
     *
     * @return $this
     * @throws Exception
     */
    public function clearConfigCache()
    {
        $list = $this->getCachedConfigFiles();
        if ($list) {
            $fs = new Filesystem();
            $fs->remove($list);
        }

        return $this;
    }

    /**
     * Clear cache
     *
     * @return $this
     * @throws Exception
     */
    public function clearIssueCache()
    {
        $list = $this->getCachedIssueDirs();
        if ($list) {
            $fs = new Filesystem();
            $fs->remove($list);
        }

        return $this;
    }

    /**
     * Get cached config files
     *
     * @return array
     * @throws \PreCommit\Exception
     */
    protected function getCachedConfigFiles()
    {
        $finder = new Finder();
        $list   = array();

        /** @var \Symfony\Component\Finder\SplFileInfo $file */
        foreach ($finder->files()->in(ConfigInstance::getCacheDir())->name('*.xml') as $file) {
            $list[] = $file->getRealpath();
        }

        return $list;
    }

    /**
     * Get issue cache directories
     *
     * @return array
     * @throws \PreCommit\Exception
     */
    protected function getCachedIssueDirs()
    {
        $finder = new Finder();
        $list   = array();

        /** @var \Symfony\Component\Finder\SplFileInfo $file */
        foreach ($finder->directories()->in(ConfigInstance::getCacheDir())->path('/^issue-.*/') as $file) {
            $list[] = $file->getRealpath();
        }

        return $list;
    }
}
