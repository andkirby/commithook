<?php
/**
 * Class test for PreCommit_Processor
 */
class PreCommit_ProcessorTest extends PHPUnit_Framework_TestCase
{
    /**
     * Php file for text hooks
     *
     * @var string
     */
    static protected $_classTest = 'tests/testsuite/PreCommit/_fixture/TestClass.php';

    /**
     * Test model
     *
     * @var \PreCommit\Processor
     */
    static protected $_model;

    /**
     * Set up test model
     */
    static public function setUpBeforeClass()
    {
        \PreCommit\Config::getInstance(array('file' => PROJECT_ROOT . '/commithook.xml'));

        $vcsAdapter = self::_getVcsAdapterMock();

        /** @var \PreCommit\Processor\PreCommit $preCommit */
        $preCommit = \PreCommit\Processor::factory('pre-commit', $vcsAdapter);
        $preCommit->setCodePath(self::_getCodePath())
            ->setFiles(array(self::$_classTest));

        $preCommit->process();
        self::$_model = $preCommit;
    }

    /**
     * Get VCS adapter mock
     *
     * @return object
     */
    protected static function _getVcsAdapterMock()
    {
        $vcsAdapter = PHPUnit_Framework_MockObject_Generator::getMock('PreCommit\Vcs\Git');
        $vcsAdapter->expects(self::once())
            ->method('getAffectedFiles')
            ->will(self::returnValue(array()));
        return $vcsAdapter;
    }

    /**
     * Get path to code root
     *
     * @return string
     */
    static protected function _getCodePath()
    {
        return realpath(__DIR__ . DIRECTORY_SEPARATOR . '../../..');
    }

    /**
     * Get specific errors list
     *
     * @param string $file
     * @param string $code
     * @return array
     * @throws PHPUnit_Framework_Exception
     */
    protected function _getSpecificErrorsList($file, $code)
    {
        $errors = self::$_model->getErrors();
        if (!isset($errors[$file])) {
            throw new PHPUnit_Framework_Exception('Errors for file ' . self::$_classTest . ' not found.');
        }
        $errors = $errors[$file];

        if (!isset($errors[$code])) {
            throw new PHPUnit_Framework_Exception("Errors for code $code not found.");
        }
        return $errors[$code];
    }

    /**
     * Test failure validation
     */
    public function testFailureValidation()
    {
        $this->assertTrue((bool) self::$_model->getErrors());
    }

    /**
     * Test CODE_PHP_DOC_MISSED
     */
    public function testPhpDocMissed()
    {
        $errors = $this->_getSpecificErrorsList(self::$_classTest, PreCommit\Validator\PhpDoc::CODE_PHP_DOC_MISSED);

        //TODO implement group comment validation
        $expected = array (
            'const WRONG = 0;',
            'protected $_param2;',
            'public function test1(){',
            'class Some_testClass extends stdClass {',
        );
        $this->_validateErrors($errors, $expected);
    }

    /**
     * @param $errors
     * @param $expected
     */
    protected function _validateErrors($errors, $expected)
    {
        $this->assertCount(count($expected), $errors);
        foreach ($expected as $i => $value) {
            $this->assertEquals($value, $errors[$i]['value']);
        }
    }
}
