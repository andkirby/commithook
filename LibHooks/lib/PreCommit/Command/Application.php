<?php
namespace PreCommit\Command;

use Symfony\Component\Console\Application as BaseApplication;

/**
 * Class Application
 *
 * @package PreCommit\Command
 */
class Application extends BaseApplication
{
    /**
     * Version
     *
     * Please update also LibHooks/config.xml
     *
     * @see LibHooks/config.xml
     */
    const VERSION = '2.0.0-dev';

    /**
     * Logo
     *
     * @var string
     */
    protected $_logo
        = <<<LOGO
 _ __  __    _ __  ,___                    __
( /  )( /  /( /  )/   /              o _/_( /  /       /
 /--'  /--/  /--'/    __ _ _   _ _   , /   /--/ __ __ /<  (
/     /  /_ /   (___/(_)/ / /_/ / /_(_(__ /  /_(_)(_)/ |_/_)_

LOGO;

    /**
     * Construct
     */
    public function __construct()
    {
        parent::__construct(
            'PHP CommitHooks',
            self::VERSION
        );
    }

    /**
     * Get help
     *
     * @return string
     */
    public function getHelp()
    {
        return $this->_logo.parent::getHelp();
    }
}
