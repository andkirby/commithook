<?php
require_once 'autoload-init.php';

use Symfony\Component\Console;
use PreCommit\Command\Application;
use PreCommit\Command\Command\Install;
use PreCommit\Command\Command\Test;
use PreCommit\Command\Command\Config;
use PreCommit\Command\Command\ClearCache;

$root = realpath(__DIR__ . '/..');

$app = new Application();
$app->add(new Install\Install($root));
$app->add(new Install\Remove($root));
$app->add(new Config\Set($root));
$app->add(new Config\IgnoreCommit($root));
$app->add(new Test($root));
$app->add(new ClearCache($root));
$app->run();
