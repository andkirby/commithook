<?php
namespace PreCommit\Composer\Command\Helper\Config;

use PreCommit\Composer\Exception;
use PreCommit\Config;
use Symfony\Component\Console\Helper\Helper;

/**
 * Helper for writing config
 *
 * @package PreCommit\Composer\Command\Helper
 */
class Set extends Helper
{
    /**
     * Helper name
     */
    const NAME = 'commithook_config_set';

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return self::NAME;
    }
}
