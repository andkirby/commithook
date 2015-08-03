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
    static protected $_adapters = array();

    /**
     * Get config instance
     *
     * @param string $key
     * @return AdapterInterface
     * @throws \PreCommit\Exception
     */
    static public function factory($key)
    {
        if (!isset(self::$_adapters[$key])) {
            $tracker = self::_getTrackerType();
            if (!$tracker) {
                return null;
            }

            $class   = self::_getConfig()->getNode('tracker/' . $tracker . '/issue/adapter/class');

            self::$_adapters[$key] = new $class($key);
            if (!(self::$_adapters[$key] instanceof AdapterInterface)) {
                throw new Exception("Class $class doesn't implement \\PreCommit\\Issue\\AdapterInterface.");
            }
        }
        return self::$_adapters[$key];
    }

    /**
     * Get tracker name
     *
     * @return string
     */
    static protected function _getTrackerType()
    {
        return (string)self::_getConfig()->getNode('tracker/type');
    }

    /**
     * Get config model
     *
     * @return Config
     */
    static protected function _getConfig()
    {
        return Config::getInstance();
    }
}
