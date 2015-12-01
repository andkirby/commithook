<?php
define('PROJECT_ROOT', realpath(__DIR__ . '/../..'));

/** @var Composer\Autoload\ClassLoader $autoloader */
require realpath(__DIR__ . '/../../..') . '/bin/autoload-init.php';

//load config
try {
    \PreCommit\Config::setSrcRootDir(__DIR__ . '/../../');
    $config = \PreCommit\Config::getInstance(array('file' => __DIR__ . '/root.xml'));
    \PreCommit\Config::mergeExtraConfig();
} catch (\Exception $e) {
    echo $e;
    exit(1);
}
