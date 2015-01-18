<?php
/** @var Composer\Autoload\ClassLoader $autoloader */
$autoloader = require_once 'autoload.php';
$autoloader->addPsr4('PreCommit\\', array(realpath(__DIR__ . '/../LibHooks/lib/PreCommit/')));
