<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
/**
 * Alias for old runner.php
 */

/**
 * Catch hook filename if it require this file
 */
if (empty($hookFile)) {
    //try to get hook name from backtrace
    $backtrace = debug_backtrace();
    if (isset($backtrace[0]['file'])) {
        $hookFile = $backtrace[0]['file'];
    } else {
        //this case will prevent to an exception...
    }
}

require_once realpath(__DIR__ . '/..') . '/src/runner.php';
