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
class FileHelper extends Helper
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
     * Get full XPath for path value
     *
     * It means for adding path 'my/protected/path' into XPath validators/FileFilter/filter/protect/path
     * the method should get base xpath (validators/FileFilter/filter/protect/path) and value ('my/protected/path').
     * Result will be validators/FileFilter/filter/protect/path/my_protected_path
     *
     * @param string $baseXpath E.g.: validators/FileFilter/filter/protect/path
     * @param string $path
     * @return null|string
     * @throws Exception
     */
    public function getXpathForPath($baseXpath, $path)
    {
        return $baseXpath.'/'.preg_replace('[^A-z0-9_-]', '_', $this->filterPath($path));
    }

    /**
     * Filter path
     *
     * @param string $path
     * @return string
     * @throws Exception
     */
    public function filterPath($path)
    {
        $updated = str_replace('\\', '/', trim($path, ' /\\'));

        if (!$updated) {
            throw new Exception("Value '{$path}' cannot be used.'");
        }

        return $updated;
    }
}
