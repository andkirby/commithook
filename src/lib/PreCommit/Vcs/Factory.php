<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */

namespace PreCommit\Vcs;

use PreCommit\Exception;

/**
 * Class abstract process adapter
 *
 * @package PreCommit\Processor
 */
class Factory
{
    /**
     * Create VCS adapter
     *
     * @param string|AdapterInterface|array $options
     * @return AdapterInterface
     */
    public static function factory($options)
    {
        return self::initVcsAdapter($options);
    }

    /**
     * Init VCS adapter
     *
     * @param string|AdapterInterface|array $options
     * @return AdapterInterface
     * @throws Exception
     */
    protected static function initVcsAdapter($options)
    {
        $vcsAdapter = null;
        if (is_object($options) && $options instanceof AdapterInterface) {
            $vcsAdapter = $options;
        } elseif (is_string($options)) {
            $vcsAdapter = self::initVcsFromString($options);
        } elseif (is_array($options)) {
            if (isset($options['vcs']) && is_string($options['vcs'])) {
                $vcsAdapter = self::initVcsFromString($options['vcs']);
            } elseif (isset($options['vcs']) && is_object($options['vcs'])
                && $options['vcs'] instanceof AdapterInterface
            ) {
                $vcsAdapter = $options['vcs'];
            }
        }

        if (!$vcsAdapter) {
            throw new Exception('VCS adapter is not set.');
        }

        if (!empty($options['vcsFiles'])) {
            //set custom affected files
            $vcsAdapter->setAffectedFiles($options['vcsFiles']);
        }

        return $vcsAdapter;
    }

    /**
     * Init VCS from string
     *
     * @param array|string $options
     * @return AdapterInterface
     */
    protected static function initVcsFromString($options)
    {
        if (strpos($options, '\\') || strpos($options, '_')) {
            $class = $options;
        } else {
            $class = '\\PreCommit\\Vcs\\'.ucfirst($options);
        }

        return new $class($options);
    }
}
