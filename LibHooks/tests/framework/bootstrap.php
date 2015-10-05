<?php
define('PROJECT_ROOT', realpath(__DIR__ . '/../..'));

if (is_dir(PROJECT_ROOT . '/../vendor/')) {
    set_include_path(
        PROJECT_ROOT . '/../vendor/' . PATH_SEPARATOR
        . get_include_path()
    );
}

/** @var Composer\Autoload\ClassLoader $autoloader */
$autoloader = require 'autoload.php';
$autoloader->addPsr4('PreCommit\\', array(realpath(PROJECT_ROOT . '/lib/PreCommit/')));
$autoloader->addPsr4('PreCommit\\Test\\', array(realpath(PROJECT_ROOT . '/tests/testsuite/PreCommit/Test/')));
set_error_handler('\PHPUnit_Util_ErrorHandler::handleError');

//load config
\PreCommit\Config::setSrcRootDir(__DIR__ . '/../../');
$config = \PreCommit\Config::getInstance(array('file' => __DIR__ . '/root.xml'));
\PreCommit\Config::mergeExtraConfig();
