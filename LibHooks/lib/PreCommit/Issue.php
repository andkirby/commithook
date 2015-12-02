<?php
namespace PreCommit;

use PreCommit\Issue\AdapterInterface;

/**
 * Issue adapters factory
 *
 * @package PreCommit
 */
class Issue
{
    /**
     * Object instance
     *
     * @var AdapterInterface[]
     */
    protected static $adapters = array();

    /**
     * Get config instance
     *
     * @param string $key
     * @return AdapterInterface
     * @throws \PreCommit\Exception
     */
    public static function factory($key)
    {
        if (!isset(self::$adapters[$key])) {
            $tracker = self::getTrackerType();
            if (!$tracker) {
                return null;
            }

            $class = self::getConfig()->getNode('tracker/'.$tracker.'/issue/adapter/class');

            self::$adapters[$key] = new $class($key);
            if (!(self::$adapters[$key] instanceof AdapterInterface)) {
                throw new Exception("Class $class doesn't implement \\PreCommit\\Issue\\AdapterInterface.");
            }
        }

        return self::$adapters[$key];
    }

    /**
     * Get tracker name
     *
     * @return string
     */
    protected static function getTrackerType()
    {
        return (string) self::getConfig()->getNode('tracker/type');
    }

    /**
     * Get config model
     *
     * @return Config
     */
    protected static function getConfig()
    {
        return Config::getInstance();
    }
}
