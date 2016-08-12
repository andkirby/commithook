<?php
$dir = null;
if (realpath(__DIR__.'/../vendor/')) {
    $dir = realpath(__DIR__.'/../vendor/');
} elseif (realpath(__DIR__.'/../../../../vendor')) {
    // package required in another composer.json
    $dir = realpath(__DIR__.'/../../../../vendor');
} else {
    die('andkirby/commithook: It looks like there are no installed required packages. '
        .'Please run "composer install" within commithook directory.');
}

/** @var Composer\Autoload\ClassLoader $autoloader */
$autoloader = require $dir.'/autoload.php';

set_error_handler('\PreCommit\ErrorHandler::handleError');

return $autoloader;
