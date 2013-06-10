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

set_error_handler('\PreCommit\ErrorHandler::handleError');

$xmlConfigFile = isset($xmlConfigFile) ? $xmlConfigFile : $rootPath . DIRECTORY_SEPARATOR . 'commithook.xml';
$config = \PreCommit\Config::getInstance(array('file' => $xmlConfigFile));

echo PHP_EOL;
echo 'Please report all hook bugs to the GitHub project.';
echo PHP_EOL . PHP_EOL;

//Get VCS type
$vcs = isset($vcs) ? $vcs : 'git';

//Process hook name
$supportedHooks = (array) $config->getNode('supported_hooks');
$supportedHooks = $supportedHooks['hook'];
if (empty($hookFile)) {
    //try to get hook name from backtrace
    $backtrace = debug_backtrace();
    if (isset($backtrace[0]['file'])) {
        $hookFile = $backtrace[0]['file'];
    } else {
        throw new \PreCommit\Exception('Error. Please add line "$hookFile = __FILE__;" in your hook file.');
    }
}
$hookName = pathinfo($hookFile, PATHINFO_BASENAME);
if (!in_array($hookName, $supportedHooks)) {
    echo "Unsupported hook '$hookName'. Please review supported_hooks nodes in configuration.";
    echo PHP_EOL . PHP_EOL;
    exit(1);
}

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
