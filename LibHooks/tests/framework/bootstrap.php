<?php
define('PROJECT_ROOT', realpath(__DIR__ . '/../..'));

/** @var Composer\Autoload\ClassLoader $autoloader */
$autoloader = require_once 'autoload.php';
$autoloader->addPsr4('PreCommit\\', array(realpath(PROJECT_ROOT . '/lib/PreCommit/')));
$autoloader->addPsr4('PreCommit\\Test\\', array(realpath(PROJECT_ROOT . '/tests/testsuite/PreCommit/Test/')));

set_error_handler('\PHPUnit_Util_ErrorHandler::handleError');
