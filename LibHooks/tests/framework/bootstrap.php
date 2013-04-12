<?php
$includePaths = array(
    get_include_path(),
    './testsuite',
    '../lib',
);
define('PROJECT_ROOT', realpath(__DIR__ . '/../..'));
set_include_path(implode(PATH_SEPARATOR, $includePaths));
require_once 'func.php';
require_once 'Autoloader.php';
\Autoloader::register();
