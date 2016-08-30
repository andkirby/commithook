<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
namespace PreCommit\Console\Command\Config\File;

use PreCommit\Console\Command\Config\AbstractConfiguredCommand;

/**
 * This command can define path for allowing to commit a file
 *
 * @package PreCommit\Console\Command\Config\File
 */
class FilesConfig extends AbstractConfiguredCommand
{
    /**
     * Get base command name
     *
     * @return string
     */
    protected function getDefinedName()
    {
        return 'files';
    }
}
