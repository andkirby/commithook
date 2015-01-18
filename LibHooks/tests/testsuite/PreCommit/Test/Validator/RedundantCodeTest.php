<?php
namespace PreCommit\Test\Validator;
use PreCommit\Processor;
use PreCommit\Config;
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
    static protected $_classTest = 'tests/testsuite/PreCommit/Test/_fixture/TestClass.php';

    /**
     * Test model
     *
     * @var Processor\PreCommit
     */
    static protected $_model;

    /**
     * Set up test model
     */
    static public function setUpBeforeClass()
    {
        //init config object
        Config::getInstance(array('file' => PROJECT_ROOT . '/commithook.xml'));

        $vcsAdapter = self::_getVcsAdapterMock();

        /** @var Processor\PreCommit $processor */
        $processor = Processor::factory('pre-commit', $vcsAdapter);
        $processor->setCodePath(PROJECT_ROOT)
            ->setFiles(array(self::$_classTest));
        $processor->process();
        self::$_model = $processor;
    }

    /**
     * Get VCS adapter mock
     *
     * @return object
     */
    protected static function _getVcsAdapterMock()
    {
        $generator = new \PHPUnit_Framework_MockObject_Generator();
        $vcsAdapter = $generator->getMock('PreCommit\Vcs\Git');
        $vcsAdapter->expects(self::once())
            ->method('getAffectedFiles')
            ->will(self::returnValue(array()));
        return $vcsAdapter;
    }

    /**
     * Get specific errors list
     *
     * @param string $file
     * @param string $code
     * @param bool $returnLines
     * @return array
     * @throws \PHPUnit_Framework_Exception
     */
    protected function _getSpecificErrorsList($file, $code, $returnLines = false)
    {
        $errors = self::$_model->getErrors();
        if (!isset($errors[$file])) {
            throw new \PHPUnit_Framework_Exception('Errors for file ' . self::$_classTest . ' not found.');
        }
        $errors = $errors[$file];

        $this->assertArrayHasKey($code, $errors);
        if (!isset($errors[$code])) {
            throw new \PHPUnit_Framework_Exception("Errors for code $code not found.");
        }

        $list = array();
        $key = $returnLines ? 'line' : 'value';
        foreach ($errors[$code] as $item) {
            if ($key == 'value' && isset($item['line'])) {
                $list[$item['line']] = $item[$key];
            } else {
                $list[] = $item[$key];
            }
        }
        return $list;
    }

    /**
     * Test having is_null() function
     */
    public function testIsNullExist()
    {
        $errors = $this->_getSpecificErrorsList(
            self::$_classTest,
            RedundantCode::CODE_IS_NULL
        );

        $expected = array (
            'if (is_null($a)) {',
        );
        $this->assertEquals($expected, array_values($errors));
    }

    /**
     * Test having intval() function
     */
    public function testIntValExist()
    {
        $this->markTestIncomplete();
        $errors = $this->_getSpecificErrorsList(
            self::$_classTest,
            RedundantCode::CODE_INTVAL
        );

        $expected = array (
            '$a = intval($a);',
        );
        $this->assertEquals($expected, array_values($errors));
    }
}
