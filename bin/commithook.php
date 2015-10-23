<?php
require_once 'autoload-init.php';

use Symfony\Component\Console;
use PreCommit\Composer\Application;
use PreCommit\Composer\Command\Install;
use PreCommit\Composer\Command\Test;
use PreCommit\Composer\Command\Config;

$root = realpath(__DIR__ . '/..');

$app = new Application();
$app->add(new Install\Install($root));
$app->add(new Install\Remove($root));
$app->add(new Config\Set($root));
$app->add(new Test($root));
$app->run();
