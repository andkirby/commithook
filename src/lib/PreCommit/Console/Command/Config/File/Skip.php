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
            $this->key = $this->isExtension() ? 'skip-ext' : 'skip';
        }

        return $this->key;
    }

    /**
     * Check if extension is requested
     *
     * @return bool
     */
    protected function isExtension()
    {
        return $this->input->hasParameterOption('--extension');
    }

    /**
     * Ignore normalizing value for extension mode
     *
     * @return $this|string
     */
    protected function normalizeValue()
    {
        if (!$this->isExtension()) {
            parent::normalizeValue();
        }

        return $this;
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
        if ($this->isExtension()) {
            $this->key = 'skip-ext';
            $this->processValue();
        } else {
            $this->key = 'skip-path';
            $this->processValue();

            $this->key = 'skip-file';
            $this->processValue();

            $this->key = 'skip';
            $this->processValue();
        }

        return $this;
    }

    /**
     * Init input definitions
     *
     * @return $this
     */
    protected function configureInput()
    {
        parent::configureInput();

        $this->addOption('extension', null, InputOption::VALUE_NONE, 'Extensions mode.');

        return $this;
    }
}
