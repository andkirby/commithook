<?php
@include_once 'func.php';
$includePaths = array(
    get_include_path(),
    './testsuite',
    '../lib',
);
define('PROJECT_ROOT', realpath(__DIR__ . '/../..'));
set_include_path(implode(PATH_SEPARATOR, $includePaths));
require_once 'Autoloader.php';
\Autoloader::register();
//set default PHPUnit error handler
set_error_handler('\PHPUnit_Util_ErrorHandler::handleError');
