<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
namespace PreCommit\Console\Command\Config\Validator\LicensePath;

use PreCommit\Console\Exception;

/**
 * This command can define path to prohibit committing changes
 *
 * @package PreCommit\Console\Command\Config
 */
class Required extends Ignored
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
            $this->key = 'required';
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
        $this->setName('validator:license:path:require');

        $help = 'This command can define path for requiring license block set.';

        $this->setHelp($help);
        $this->setDescription($help);

        return $this;
    }
}
