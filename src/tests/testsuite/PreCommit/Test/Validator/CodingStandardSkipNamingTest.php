<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
namespace PreCommit\Test\Validator;

use PreCommit\Config;
use PreCommit\Processor;
use PreCommit\Validator\CodingStandard;
use PreCommit\Vcs\Git;

/**
 * Class test for Processor
 */
class CodingStandardSkipNamingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Php file for text hooks
     *
     * @var string
     */
    protected static $classTest = 'tests/testsuite/PreCommit/Test/_fixture/TestClassSkipNaming.php';

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
        $processor = Processor::factory('pre-commit', $vcsAdapter);
        $processor->setCodePath(PROJECT_ROOT)
            ->setFiles([self::$classTest]);
        $processor->process();
        self::$model = $processor;
    }

    /**
     * Test skip method naming validation
     */
    public function testSkipPublicFunctionNaming()
    {
        $errors = $this->getSpecificErrorsList(
            self::$classTest,
            CodingStandard::CODE_PHP_PUBLIC_METHOD_NAMING_INVALID
        );
        $this->assertEmpty(array_values($errors));

        $errors = $this->getSpecificErrorsList(
            self::$classTest,
            CodingStandard::CODE_PHP_PROTECTED_METHOD_NAMING_INVALID
        );
        $this->assertEmpty(array_values($errors));
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
            return [];
        }
        $errors = $errors[$file];

        if (!isset($errors[$code])) {
            return [];
        }

        $list = [];
        $key  = $returnLines ? 'line' : 'value';
        foreach ($errors[$code] as $item) {
            if ($key == 'value' && isset($item['line'])) {
                $list[$item['line']] = $item[$key];
            } else {
                $list[] = $item[$key];
            }
        }

        return $list;
    }
}
