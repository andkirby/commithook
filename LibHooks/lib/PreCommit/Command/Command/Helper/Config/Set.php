<?php
namespace PreCommit\Command\Command\Helper\Config;

use Symfony\Component\Console\Helper\Helper;

/**
 * Helper for writing config
 *
 * @package PreCommit\Command\Command\Helper
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
