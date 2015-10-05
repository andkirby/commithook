<?php
/**
 * End point file to run CommitHooks
 *
 * @deprecated Deprecated to direct using since v1.6.4.
 *             All code will be pushed to use /bin/runner.php
 * @see /bin/runner.php
 */
/** stub */

!defined('COMMIT_HOOKS_ROOT') && define('COMMIT_HOOKS_ROOT', realpath(__DIR__ . '/..'));
!defined('TEST_MODE') && define('TEST_MODE', false);
set_include_path(
    implode(
        PATH_SEPARATOR,
        array(
            get_include_path(),
            COMMIT_HOOKS_ROOT . '/LibHooks/lib',
        )
    )
);

//init autoloader
require_once __DIR__ . '/../bin/autoload-init.php';

set_error_handler('\PreCommit\ErrorHandler::handleError');

//Get VCS type
$vcs = isset($vcs) ? $vcs : 'git';

//load config
if (!isset($rootConfigFile)) {
    $rootConfigFile = COMMIT_HOOKS_ROOT . '/LibHooks/config/root.xml';
}
$config = \PreCommit\Config::getInstance(array('file' => $rootConfigFile));

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

//set work directories
PreCommit\Config::setProjectDir(
    realpath(pathinfo($hookFile, PATHINFO_DIRNAME) . '/../..')
);
PreCommit\Config::setRootDir(COMMIT_HOOKS_ROOT . '/LibHooks');

if (!PreCommit\Config::loadCache()) {
    PreCommit\Config::mergeExtraConfig();
}

/** @var \PreCommit\Processor\AbstractAdapter $processor */
$processor = \PreCommit\Processor::factory($hookName, $vcs);
$processor->process();

if (!$processor->getErrors()) {
    echo PreCommit\Config::getInstance()->getNode("hook/$hookName/end_message/success");
    echo PHP_EOL . PHP_EOL;
    exit(0);
} else {
    echo PreCommit\Config::getInstance()->getNode("hook/$hookName/end_message/error");
    echo PHP_EOL . PHP_EOL;
    echo $processor->getErrorsOutput();
    echo PHP_EOL . PHP_EOL;
    exit(1);
}
