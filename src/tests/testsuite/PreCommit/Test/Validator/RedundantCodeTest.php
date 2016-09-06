<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
namespace PreCommit\Test\Validator;

use PreCommit\Config;
use PreCommit\Processor;
use PreCommit\Validator\RedundantCode;

/**
 * Class test for Processor
 */
class RedundantCodeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Php file for text hooks
     *
     * @var string
     */
    protected static $classTest = 'tests/testsuite/PreCommit/Test/_fixture/TestClass.php';

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
        $vcsAdapter = self::getVcsAdapterMock();

        /** @var Processor\PreCommit $processor */
        $processor = Processor::factory('pre-commit', $vcsAdapter);
        $processor->setCodePath(PROJECT_ROOT)
            ->setFiles([self::$classTest]);
        $processor->process();
        self::$model = $processor;
    }

    /**
     * Test having is_null() function
     */
    public function testIsNullExist()
    {
        $errors = $this->getSpecificErrorsList(
            self::$classTest,
            RedundantCode::CODE_IS_NULL
        );

        $expected = [
            'if (is_null($a)) {',
        ];
        $this->assertEquals($expected, array_values($errors));
    }

    /**
     * Test having intval() function
     */
    public function testIntValExist()
    {
        $this->markTestIncomplete();
        $errors = $this->getSpecificErrorsList(
            self::$classTest,
            RedundantCode::CODE_INTVAL
        );

        $expected = [
            '$a = intval($a);',
        ];
        $this->assertEquals($expected, array_values($errors));
    }

    /**
     * Get VCS adapter mock
     *
     * @return object
     */
    protected static function getVcsAdapterMock()
    {
        $generator  = new \PHPUnit_Framework_MockObject_Generator();
        $vcsAdapter = $generator->getMock('PreCommit\Vcs\Git');
        $vcsAdapter->expects(self::once())
            ->method('getAffectedFiles')
            ->will(self::returnValue([]));

        return $vcsAdapter;
    }

    /**
     * Get specific errors list
     *
     * @param string $file
     * @param string $code
     * @param bool   $returnLines
     * @return array
     * @throws \PHPUnit_Framework_Exception
     */
    protected function getSpecificErrorsList($file, $code, $returnLines = false)
    {
        $errors = self::$model->getErrors();
        if (!isset($errors[$file])) {
            throw new \PHPUnit_Framework_Exception('Errors for file '.self::$classTest.' not found.');
        }
        $errors = $errors[$file];

        $this->assertArrayHasKey($code, $errors);
        if (!isset($errors[$code])) {
            throw new \PHPUnit_Framework_Exception("Errors for code $code not found.");
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
