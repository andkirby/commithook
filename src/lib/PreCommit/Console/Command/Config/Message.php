<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
namespace PreCommit\Console\Command\Config;

/**
 * Message command for set up commit message configuration
 *
 * @package PreCommit\Console\Command\Config
 */
class Message extends AbstractConfiguredCommand
{
    /**
     * {@inheritdoc}
     */
    protected function getDefinedName()
    {
        return 'message';
    }
}
