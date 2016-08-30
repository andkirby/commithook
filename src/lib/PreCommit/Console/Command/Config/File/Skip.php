<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
namespace PreCommit\Console\Command\Config\File;

use PreCommit\Console\Exception;
use Symfony\Component\Console\Input\InputOption;

/**
 * This command can define path for skipping validation
 *
 * @package PreCommit\Console\Command\Config\File
 */
class Skip extends Protect
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
            $this->key = 'skip';
        }

        return $this->key;
    }

    /**
     * Init default helpers
     *
     * @return $this
     */
    protected function configureCommand()
    {
        $this->setName('files:skip');

        $help = 'This command can define file/directory path to skip validation.';

        $this->setHelp($help);
        $this->setDescription($help);

        return $this;
    }

    /**
     * Show set values
     *
     * @return $this
     */
    protected function showSetValues()
    {
        $this->key = 'skip';
        $this->processValue();

        return $this;
    }
}
