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

$xmlConfigFile = isset($xmlConfigFile) ? $xmlConfigFile : $rootPath . DIRECTORY_SEPARATOR . 'pre-commit.xml';
$config = \PreCommit\Config::getInstance(array('file' => $xmlConfigFile));

echo PHP_EOL;
echo 'Please report all hook bugs to the GitHub project.';
echo PHP_EOL . PHP_EOL;

$codePath = isset($codePath) ? $codePath : realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '../../..');
$changedFiles = array_filter(explode("\n", `git diff --cached --name-only --diff-filter=ACM`));

$preCommit = new \PreCommit\Processor();
$preCommit->setCodePath($codePath)
    ->setFiles($changedFiles);

$preCommit->process();

if (!$preCommit->getErrors()) {
    echo 'Good job! Have successes! ;)';
    echo PHP_EOL . PHP_EOL;
    exit(0);
} else {
    echo 'Something wrong in the code. Please fix issues below:';
    echo PHP_EOL . PHP_EOL;

    foreach ($preCommit->getErrors() as $file => $fileErrors) {
        echo "======== $file =========\n";
        foreach ($fileErrors as $errorsType) {
            foreach ($errorsType as $error) {
                echo str_replace(array("\n", PHP_EOL), '', $error['message']) . "\n";
            }
        }
    }
    echo PHP_EOL . PHP_EOL;
    exit(1);
}
