<?php
// @codingStandardsIgnoreFile
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
define('PROJECT_ROOT', realpath(__DIR__ . '/../..'));
/** @var Composer\Autoload\ClassLoader $autoloader */
require realpath(__DIR__ . '/../../..') . '/bin/autoload-init.php';

//load config
try {
    \PreCommit\Config::setSrcRootDir(__DIR__ . '/../../');
    $config = \PreCommit\Config::initInstance(array('file' => __DIR__.'/root.xml'));
    \PreCommit\Config::mergeExtraConfig();
} catch (\Exception $e) {
    echo $e;
    exit(1);
}
