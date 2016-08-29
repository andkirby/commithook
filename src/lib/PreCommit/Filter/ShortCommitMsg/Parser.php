<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
/**
 * Created by PhpStorm.
 * User: a.roslik
 * Date: 8/29/16 029
 * Time: 7:01 PM
 */

namespace PreCommit\Filter\ShortCommitMsg;

use PreCommit\Config;
use PreCommit\Exception;
use PreCommit\Filter\ShortCommitMsg\Parser\IssueParserInterface;
use PreCommit\Interpreter\InterpreterInterface;

/**
 * Fabric class Parser
 *
 * @package PreCommit\Filter\ShortCommitMsg
 */
class Parser
{
    /**
     * Object instance
     *
     * @var InterpreterInterface[]
     */
    protected static $adapters = [];

    /**
     * Get config instance
     *
     * @param string $type Commit message type group
     * @return InterpreterInterface|IssueParserInterface
     * @throws \PreCommit\Exception
     */
    public static function factory($type)
    {
        if (!isset(self::$adapters[$type])) {
            $tracker = self::getTrackerType();
            if (!$tracker) {
                return null;
            }

            $xpath = 'tracker/'.$tracker.'/message/parser/class';
            $class = self::getConfig()->getNode($xpath);

            if (!$class) {
                throw new Exception(
                    'Parser class is not defined in in Xpath "'.$xpath.'".'
                );
            }
            self::$adapters[$type] = new $class(['type' => $type]);
            if (!(self::$adapters[$type] instanceof InterpreterInterface)
                || !(self::$adapters[$type] instanceof IssueParserInterface)
            ) {
                throw new Exception(
                    "Class $class doesn't implement ".InterpreterInterface::class." and ".IssueParserInterface::class."."
                );
            }
        }

        return self::$adapters[$type];
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
