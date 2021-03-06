<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
/**
 * End point file to run CommitHooks
 *
 * @see        bin/runner.php
 * @deprecated Deprecated to direct using since v1.6.4.
 *             All code will be pushed to use /bin/runner.php
 */
// @codingStandardsIgnoreFile
/**
 * Stub
 */
!defined('COMMIT_HOOKS_ROOT') && define('COMMIT_HOOKS_ROOT', realpath(__DIR__.'/..'));
!defined('TEST_MODE') && define('TEST_MODE', false);
!defined('GIT_BIN') && define('GIT_BIN', 'git');

set_include_path(
    implode(
        PATH_SEPARATOR,
        array(
            get_include_path(),
            COMMIT_HOOKS_ROOT.'/src/lib',
        )
    )
);

//init autoloader
require_once __DIR__.'/../bin/autoload-init.php';

//Get VCS type
$vcs = isset($vcs) ? $vcs : 'git';

//Get VCS files
$vcsFiles = isset($vcsFiles) ? $vcsFiles : null;

//load config
if (!isset($rootConfigFile)) {
    $rootConfigFile = COMMIT_HOOKS_ROOT.'/src/config/root.xml';
}
$config = \PreCommit\Config::initInstance(array('file' => $rootConfigFile));

//prepare head block for output
$output         = array();
$output['head'] = 'PHP CommitHooks v'.$config->getNode('version');
$output['head'] .= PHP_EOL;
$output['head'] .= 'Please report all hook bugs to the GitHub project.';
$output['head'] .= PHP_EOL;
$output['head'] .= 'http://github.com/andkirby/commithook';
$output['head'] .= PHP_EOL.PHP_EOL;

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
        echo PHP_EOL.PHP_EOL;
        exit(1);
    }
}

$hookName = pathinfo($hookFile, PATHINFO_BASENAME);
if (!in_array($hookName, $supportedHooks)) {
    echo "Unsupported hook '$hookName'. Please review supported_hooks nodes in configuration.";
    echo PHP_EOL.PHP_EOL;
    exit(1);
}

$vcsAdapter = \PreCommit\Vcs\Factory::factory(
    array(
        'vcs'      => $vcs,
        'vcsFiles' => $vcsFiles,
    )
);

// init code path
$vcsAdapter->getCodePath(
//get ".git" directory
    realpath(pathinfo($hookFile, PATHINFO_DIRNAME).'/..')
);

//set work directory through GIT commands
PreCommit\Config::setProjectDir(
    $vcsAdapter->getCodePath()
);
PreCommit\Config::setSrcRootDir(COMMIT_HOOKS_ROOT.'/src');

if (!PreCommit\Config::loadCache()) {
    PreCommit\Config::mergeExtraConfig();
}
try {
    /**
     * @var \PreCommit\Processor\AbstractAdapter $processor
     */
    $processor = \PreCommit\Processor::factory(
        $hookName,
        array(
            //init VCS before creation
            'vcs' => $vcsAdapter,
        )
    );

    $processor->process();

    //show head block
    if (PreCommit\Config::getInstance()->getNode('output/show_head')) {
        echo $output['head'];
    }

    if (!$processor->getErrors()) {
        echo PreCommit\Config::getInstance()->getNode("hooks/$hookName/end_message/success");
        echo PHP_EOL;
        $processor->dispatchEvent('success_end');
        $processor->dispatchEvent('end', 0);
        echo PHP_EOL;
        exit(0);
    } else {
        echo PreCommit\Config::getInstance()->getNode("hooks/$hookName/end_message/error");
        echo PHP_EOL;
        $processor->dispatchEvent('error_end');
        $processor->dispatchEvent('end', 1);
        echo $processor->getErrorsOutput();
        echo PHP_EOL;
        exit(PreCommit\Console\Exception::CODE_VALIDATION);
    }
} catch (PreCommit\Exception $e) {
    if (TEST_MODE) {
        throw $e;
    }
    echo 'Error: '.$e->getMessage();
    echo PHP_EOL;
    exit(PreCommit\Console\Exception::CODE_INTERNAL);
} catch (\Exception $e) {
    if (TEST_MODE) {
        throw $e;
    }
    echo 'Exception: '.$e->getMessage();
    echo PHP_EOL;
    echo $e->getTraceAsString();
    echo PHP_EOL;
    exit(PreCommit\Console\Exception::CODE_FATAL);
}
