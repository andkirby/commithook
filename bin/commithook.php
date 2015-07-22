<?php
require_once 'autoload-init.php';

use Symfony\Component\Console;
use PreCommit\Composer\Application;
use PreCommit\Composer\Command\Install;
use PreCommit\Composer\Command\Remove;
use PreCommit\Composer\Command\Test;

$app = new Application();
$app->add(new Install(realpath(__DIR__ . '/..')));
$app->add(new Remove(realpath(__DIR__ . '/..')));
$app->add(new Test(realpath(__DIR__ . '/..')));
$app->run();
