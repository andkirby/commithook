<?php
$rootPath = __DIR__;
set_include_path(
    implode(
        PATH_SEPARATOR,
        array(
            get_include_path(),
            $rootPath . '/lib',
        )
    )
);

require_once 'lib/PreCommit/Autoloader.php';
\PreCommit\Autoloader::register();

$xmlConfigFile = isset($xmlConfigFile) ? $xmlConfigFile : $rootPath . DIRECTORY_SEPARATOR . 'commithook.xml';
$config = \PreCommit\Config::getInstance(array('file' => $xmlConfigFile));

echo PHP_EOL;
echo 'Please report all hook bugs to the GitHub project.';
echo PHP_EOL . PHP_EOL;

$vcs = isset($vcs) ? $vcs : 'git';

/** @var \PreCommit\Processor\AbstractAdapter $processor */
$processor = \PreCommit\Processor::factory($hookName, $vcs);
$processor->process();

if (!$processor->getErrors()) {
    echo 'Good job! Have successes! ;)';
    echo PHP_EOL . PHP_EOL;
    exit(0);
} else {
    echo 'Something wrong in the code. Please fix issues below:';
    echo PHP_EOL . PHP_EOL;
    echo $processor->getErrorsOutput();
    echo PHP_EOL . PHP_EOL;
    exit(1);
}
