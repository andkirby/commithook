<?php
use \PreCommit\Processor\ErrorCollector as Error;

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
        \PreCommit\Config::getInstance(array('file' => PROJECT_ROOT . '/pre-commit.xml'));
        $preCommit = new \PreCommit\Processor();
        $preCommit->setCodePath(self::_getCodePath())
            ->setFiles(array(self::$_classTest));

        $preCommit->process();
        self::$_model = $preCommit;
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
            'class Some_testClass extends stdClass',
        );
        $this->assertCount(4, $errors);
        $this->assertEquals($expected[0], $errors[0]['value']);
        $this->assertEquals($expected[1], $errors[1]['value']);
        $this->assertEquals($expected[2], $errors[2]['value']);
        $this->assertEquals($expected[3], $errors[3]['value']);
    }

    /**
     * Test CODE_PHP_DOC_MESSAGE
     */
    public function testPhpDocMessageMissed()
    {
        $errors = $this->_getSpecificErrorsList(self::$_classTest, PreCommit\Validator\PhpDoc::CODE_PHP_DOC_MESSAGE);

        //TODO implement group comment validation
        $expected = array (
            'protected $_param3;',
            'public function someFuncWithoutMessage()',
        );
        $this->assertCount(2, $errors);
        $this->assertEquals($expected[0], $errors[0]['value']);
        $this->assertEquals($expected[1], $errors[1]['value']);
    }
}
