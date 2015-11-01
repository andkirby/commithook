<?php
require_once 'autoload-init.php';

use Symfony\Component\Console;
use PreCommit\Command\Application;
use PreCommit\Command\Command\Install;
use PreCommit\Command\Command\Test;
use PreCommit\Command\Command\Config;

$root = realpath(__DIR__ . '/..');

$app = new Application();
$app->add(new Install\Install($root));
$app->add(new Install\Remove($root));
$app->add(new Config\Set($root));
$app->add(new Config\IgnoreCommit($root));
$app->add(new Test($root));
$app->run();
