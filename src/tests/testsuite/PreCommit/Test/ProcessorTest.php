<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
namespace PreCommit\Test;

use PreCommit\Config;
use PreCommit\Processor;
use PreCommit\Processor\PreCommit;
use PreCommit\Validator\PhpDoc;
use PreCommit\Vcs\Git;

/**
 * Class test for Processor
 */
class ProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Php file for text hooks
     *
     * @var string
     */
    protected static $classTest = 'testsuite/PreCommit/Test/_fixture/TestClass.php';

    /**
     * Test model
     *
     * @var PreCommit
     */
    protected static $model;

    /**
     * Set up test model
     */
    public static function setUpBeforeClass()
    {
        Config::initInstance(['file' => PROJECT_ROOT.'/config/root.xml']);
        Config::setSrcRootDir(PROJECT_ROOT);

        $vcsAdapter = self::getVcsAdapterMock();

        /** @var PreCommit $preCommit */
        $preCommit = Processor::factory('pre-commit', $vcsAdapter);
        $preCommit->setCodePath(self::getCodePath())
            ->setFiles([self::$classTest]);

        $preCommit->process();
        self::$model = $preCommit;
    }

    /**
     * Test failure validation
     */
    public function testFailureValidation()
    {
        $this->assertTrue((bool) self::$model->getErrors());
    }

    /**
     * Test CODE_PHP_DOC_MISSED
     */
    public function testPhpDocMissed()
    {
        $errors = $this->getSpecificErrorsList(self::$classTest, PhpDoc::CODE_PHP_DOC_MISSED);

        //TODO implement group comment validation
        $expected = [
            'const WRONG = 0;',
            'protected $_param2;',
            'public function test1(){',
            'class Some_testClass extends stdClass {',
        ];
        $this->validateErrors($errors, $expected);
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
     * Get path to code root
     *
     * @return string
     */
    protected static function getCodePath()
    {
        return realpath(__DIR__.DIRECTORY_SEPARATOR.'../../..');
    }

    /**
     * Get specific errors list
     *
     * @param string $file
     * @param string $code
     * @return array
     * @throws \PHPUnit_Framework_Exception
     */
    protected function getSpecificErrorsList($file, $code)
    {
        $errors = self::$model->getErrors();
        if (!isset($errors[$file])) {
            throw new \PHPUnit_Framework_Exception('Errors for file '.self::$classTest.' not found.');
        }
        $errors = $errors[$file];

        if (!isset($errors[$code])) {
            throw new \PHPUnit_Framework_Exception("Errors for code $code not found.");
        }

        return $errors[$code];
    }

    /**
     * Validate errors list
     *
     * @param array $errors
     * @param array $expected
     */
    protected function validateErrors($errors, $expected)
    {
        $this->assertCount(count($expected), $errors);
        foreach ($expected as $i => $value) {
            $this->assertEquals($value, $errors[$i]['value']);
        }
    }
}
