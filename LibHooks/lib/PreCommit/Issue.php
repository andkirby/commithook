<?php
/**
 * OnePica
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to codemaster@onepica.com so we can send you a copy immediately.
 *
 * @category  OnePica
 * @package   OnePica_${MODULE}
 * @copyright Copyright (c) 2012 One Pica, Inc. (http://www.onepica.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

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
     * @var $this
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
            $tracker = self::_getTrackerName();
            if (!$tracker) {
                return null;
            }

            $class   = self::_getConfig()->getNode('tracker/' . $tracker . '/adapter/issue/class');

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
    static protected function _getTrackerName()
    {
        return (string)self::_getConfig()->getNode('tracker_name');
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
