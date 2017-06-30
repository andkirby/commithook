<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
namespace PreCommit\Console;

use PreCommit\Exception as TopException;

/**
 * Class Exception
 *
 * @package PreCommit\Command
 */
class Exception extends TopException
{
    /**#@+
     * Console end error
     */
    const CODE_FATAL      = 1;
    const CODE_INTERNAL   = 2;
    const CODE_LOGIC      = 4;
    const CODE_VALIDATION = 6;
    /**#@-*/
}
