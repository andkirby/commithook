<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
namespace PreCommit\Console;

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
     * Please update also src/config.xml
     *
     * @see src/config.xml
     */
    const VERSION = '2.0.x-dev';

// @codingStandardsIgnoreStart
    /**
     * Logo
     *
     * @var string
     */
    protected $logo
        = <<<LOGO
 _ __  __    _ __  ,___                    __
( /  )( /  /( /  )/   /              o _/_( /  /       /
 /--'  /--/  /--'/    __ _ _   _ _   , /   /--/ __ __ /<  (
/     /  /_ /   (___/(_)/ / /_/ / /_(_(__ /  /_(_)(_)/ |_/_)_

LOGO;
// @codingStandardsIgnoreEnd

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
        return $this->logo.parent::getHelp();
    }
}
