<?php
require_once 'autoload-init.php';

use PreCommit\Console\Application;
use PreCommit\Console\Command\ClearCache;
use PreCommit\Console\Command\Config;
use PreCommit\Console\Command\Install;
use PreCommit\Console\Command\Test;
use PreCommit\Console\Command\Validator;

$root = realpath(__DIR__ . '/..');

$app = new Application();
$app->add(new Install\Install($root));
$app->add(new Install\Remove($root));
$app->add(new Config\Set($root));
$app->add(new Config\IgnoreCommit($root));
$app->add(new Config\Tracker\Task($root));
$app->add(new Config\File\Skip($root));
$app->add(new Config\File\Protect($root));
$app->add(new Config\File\Allow($root));
$app->add(new Test($root));
$app->add(new ClearCache($root));
$app->add(new Validator\Disable($root));
$app->add(new Validator\ListAll($root));
$app->run();
