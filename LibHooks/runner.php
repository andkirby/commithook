<?php
/**
 * End point file to run CommitHooks
 *
 * @deprecated Deprecated to direct using since v1.6.4.
 *             All code will be pushed to use /bin/runner.php
 * @see /bin/runner.php
 */
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

//Get VCS type
$vcs = isset($vcs) ? $vcs : 'git';

//load config
$config = \PreCommit\Config::getInstance(array('file' => $rootPath . DIRECTORY_SEPARATOR . 'config.xml'));

echo PHP_EOL;
echo 'PHP CommitHooks v' . $config->getNode('version');
echo PHP_EOL;
echo 'Please report all hook bugs to the GitHub project.';
echo PHP_EOL;
echo 'http://github.com/andkirby/commithook';
echo PHP_EOL . PHP_EOL;

//Process hook name
$supportedHooks = $config->getNodeArray('supported_hooks');
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

$projectDir = PreCommit\Config::getProjectDir($hookFile);
if (!PreCommit\Config::loadCache($rootPath, $projectDir)) {
    PreCommit\Config::mergeExtraConfig($rootPath, $projectDir);
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
