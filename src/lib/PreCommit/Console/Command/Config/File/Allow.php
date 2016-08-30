<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
namespace PreCommit\Console\Command\Config\File;

use PreCommit\Console\Exception;

/**
 * This command can define path for allowing to commit a file
 *
 * @package PreCommit\Console\Command\Config\File
 */
class Allow extends Protect
{
    /**
     * Get key name
     *
     * @return string
     * @throws Exception
     */
    protected function getKey()
    {
        if (null === $this->key) {
            $this->key = 'allow';
        }

        return $this->key;
    }

    /**
     * Show set values
     *
     * @return $this
     */
    protected function showSetValues()
    {
        $this->key = 'allow';
        $this->processValue();

        return $this;
    }

    /**
     * Init default helpers
     *
     * @return $this
     */
    protected function configureCommand()
    {
        $this->setName('files:allow');

        $help = 'This command can define path to prohibit committing changes.';

        $this->setHelp($help);
        $this->setDescription($help);

        return $this;
    }
}
