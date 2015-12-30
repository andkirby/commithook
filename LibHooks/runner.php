<?php
/**
 * End point file to run CommitHooks
 *
 * @see        bin/runner.php
 * @deprecated Deprecated to direct using since v1.6.4.
 *             All code will be pushed to use /bin/runner.php
 */

/**
 * Stub
*/
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

//Get VCS type
$vcs = isset($vcs) ? $vcs : 'git';

//load config
if (!isset($rootConfigFile)) {
    $rootConfigFile = COMMIT_HOOKS_ROOT . '/LibHooks/config/root.xml';
}
$config = \PreCommit\Config::getInstance(array('file' => $rootConfigFile));

//prepare head block for output
$output = array();
$output['head'] = 'PHP CommitHooks v' . $config->getNode('version');
$output['head'] .= PHP_EOL;
$output['head'] .= 'Please report all hook bugs to the GitHub project.';
$output['head'] .= PHP_EOL;
$output['head'] .= 'http://github.com/andkirby/commithook';
$output['head'] .= PHP_EOL . PHP_EOL;

//Process hook name
$supportedHooks = $config->getNodeArray('supported_hooks');
$supportedHooks = $supportedHooks['hook'];
if (empty($hookFile)) {
    //try to get hook name from backtrace
    $backtrace = debug_backtrace();
    if (isset($backtrace[0]['file'])) {
        $hookFile = $backtrace[0]['file'];
    } else {
        echo 'Error. Please add line "$hookFile = __FILE__;" in your hook file.';
        echo PHP_EOL . PHP_EOL;
        exit(1);
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
PreCommit\Config::setSrcRootDir(COMMIT_HOOKS_ROOT . '/LibHooks');

if (!PreCommit\Config::loadCache()) {
    PreCommit\Config::mergeExtraConfig();
}

try {
    /**
     * @var \PreCommit\Processor\AbstractAdapter $processor
     */
    $processor = \PreCommit\Processor::factory($hookName, $vcs);
    $processor->process();
} catch (\Exception $e) {
    echo 'EXCEPTION:'.$e->getMessage();
    echo PHP_EOL;
    echo $e->getTraceAsString();
    echo PHP_EOL . PHP_EOL;
    exit(1);
}

//show head block
if (PreCommit\Config::getInstance()->getNode('output/show_head')) {
    echo $output['head'];
}

if (!$processor->getErrors()) {
    echo PreCommit\Config::getInstance()->getNode("hooks/$hookName/end_message/success");
    $processor->dispatchEvent('success_end');
    $processor->dispatchEvent('end', 0);
    echo PHP_EOL . PHP_EOL;
    exit(0);
} else {
    echo PreCommit\Config::getInstance()->getNode("hooks/$hookName/end_message/error");
    echo PHP_EOL . PHP_EOL;
    $processor->dispatchEvent('error_end');
    $processor->dispatchEvent('end', 1);
    echo $processor->getErrorsOutput();
    echo PHP_EOL . PHP_EOL;
    exit(1);
}
