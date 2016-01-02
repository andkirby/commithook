<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
namespace PreCommit;

/**
 * Error handler that converts PHP errors and warnings to exceptions.
 */
class ErrorHandler
{
    /**
     * Handle error method
     *
     * @param  int     $errNo
     * @param  string  $errStr
     * @param  string  $errFile
     * @param  integer $errLine
     * @return bool
     * @throws Exception
     */
    public static function handleError($errNo, $errStr, $errFile, $errLine)
    {
        if (!($errNo & error_reporting())) {
            return false;
        }

        $trace = debug_backtrace(false);
        array_shift($trace);

        foreach ($trace as $frame) {
            if ($frame['function'] == '__toString') {
                return false;
            }
        }

        $message = "PHPError [$errNo] $errStr at file $errFile:$errLine";
        throw new Exception($message);
    }
}
