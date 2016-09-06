<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
namespace PreCommit\Test\Validator;

use PreCommit\Config;
use PreCommit\Processor;
use PreCommit\Vcs\Git;

/**
 * Base test case class for testing with the Processor
 */
class PreCommitFlowTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * Php file for text hooks
     *
     * @var string
     */
    protected static $fileTest;

    /**
     * Test model
     *
     * @var Processor\PreCommit
     */
    protected static $model;

    /**
     * Set up test model
     */
    public static function setUpBeforeClass()
    {
        //init config object
        Config::initInstance(['file' => PROJECT_ROOT.'/config/root.xml']);
        Config::setSrcRootDir(PROJECT_ROOT);
        Config::mergeExtraConfig();

        $vcsAdapter = self::getVcsAdapterMock();

        /** @var Processor\PreCommit $processor */
        $processor = Processor::factory('pre-commit', ['vcs' => $vcsAdapter]);
        $processor->setCodePath(PROJECT_ROOT)
            ->setFiles([self::$fileTest]);
        $processor->process();
        self::$model = $processor;
    }

    /**
     * Get VCS adapter mock
     *
     * @return object
     */
    protected static function getVcsAdapterMock()
    {
        $vcsAdapter = new Git();
        $vcsAdapter->setAffectedFiles([]);

        return $vcsAdapter;
    }

    /**
     * Get specific errors list
     *
     * @param string $file
     * @param string $code
     * @param bool   $returnLines
     * @param object $model
     * @return array
     * @throws \PHPUnit_Framework_Exception
     */
    protected function getSpecificErrorsList($file, $code, $returnLines = false, $model = null)
    {
        if (!$model) {
            $model = self::$model;
        }
        $errors = $model->getErrors();
        if (!isset($errors[$file])) {
            throw new \PHPUnit_Framework_Exception('Errors for file "'.static::$fileTest.'" not found.');
        }
        $errors = $errors[$file];

        $this->assertArrayHasKey($code, $errors);
        if (!isset($errors[$code])) {
            throw new \PHPUnit_Framework_Exception("Errors for code $code not found.");
        }

        $list = [];
        $key  = $returnLines ? 'line' : 'value';
        foreach ($errors[$code] as $item) {
            if ($key == 'value' && isset($item['line']) && isset($item['value'])) {
                if (is_array($item['line'])) {
                    foreach ($item['line'] as $line) {
                        $list[$line] = $item['value'];
                    }
                } else {
                    $list[$item['line']] = $item['value'];
                }
            } elseif (isset($item['value'])) {
                $list[] = $item['value'];
            } else {
                $list[] = $item[$key];
            }
        }

        return $list;
    }

    /**
     * Get content of the test file
     *
     * @return string
     */
    protected static function getTestFileContent()
    {
        return file_get_contents(__DIR__.'/../../../../../'.static::$fileTest);
    }
}
