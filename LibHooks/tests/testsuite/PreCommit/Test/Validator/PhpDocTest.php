<?php
namespace PreCommit\Test\Validator;
use PreCommit\Processor;
use PreCommit\Config;
use PreCommit\Validator\PhpDoc;

/**
 * Class test for Processor
 */
class PhpDocTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Php file for text hooks
     *
     * @var string
     */
    static protected $_fileTest = 'tests/testsuite/PreCommit/Test/_fixture/TestPhpDocClass.php';

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
            ->setFiles(array(self::$_fileTest));
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
            throw new \PHPUnit_Framework_Exception('Errors for file ' . self::$_fileTest . ' not found.');
        }
        $errors = $errors[$file];

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
     * Test CODE_PHP_DOC_MISSED
     */
    public function testPhpDocBlockMissed()
    {
        $errors = $this->_getSpecificErrorsList(
            self::$_fileTest,
            PhpDoc::CODE_PHP_DOC_MISSED
        );

        $expected = array (
            'const WRONG = 0; //PhpDoc is missed',
            'protected $_paramB; //PhpDoc is missed',
            'protected $_paramBB; //PhpDoc is missed',
            'public function test1() //PhpDoc is missed',
            'class Some_testClassPhpDoc extends stdClass //PhpDoc is missed',
        );
        $this->assertEquals($expected, array_values($errors));
    }

    /**
     * Test CODE_PHP_DOC_MESSAGE
     */
    public function testPhpDocDescriptionMissed()
    {
        $errors = $this->_getSpecificErrorsList(
            self::$_fileTest,
            PhpDoc::CODE_PHP_DOC_MESSAGE
        );

        $expected = array (
            '    /**
     * @var string
     */',
            '    /**
     *
     * @var string
     */',
            '    /**
     * @var string      Here PHPDoc message missed
     */',
            '    /**
     * @param int $param
     */',
            '    /**
     *
     * @param int $param
     */',
            '    /**
     * lowercase name
     *
     * @param int $param
     */',
            '    /**
     *
     * Test extra gap 4
     *
     * @param int $param
     */',
        );
        $this->assertEquals($expected, array_values($errors));
    }

    /**
     * Test CODE_PHP_DOC_MISSED_GAP
     */
    public function testPhpDocMissedGap()
    {
        $errors = $this->_getSpecificErrorsList(
            self::$_fileTest,
            PhpDoc::CODE_PHP_DOC_MISSED_GAP
        );

        $expected = array (
            '     * Missed gap in phpDoc',
            '     * Missed gap in phpDoc 2',
        );
        $this->assertEquals($expected, array_values($errors));
    }

    /**
     * Test CODE_PHP_DOC_ENTER_DESCRIPTION
     */
    public function testPhpDocEnterDescription()
    {
        $errors = $this->_getSpecificErrorsList(
            self::$_fileTest,
            PhpDoc::CODE_PHP_DOC_ENTER_DESCRIPTION
        );

        $expected = 2;
        $this->assertCount($expected, $errors);
    }

    /**
     * Test CODE_PHP_DOC_MESSAGE
     */
    public function testPhpDocUnknownDescription()
    {
        $errors = $this->_getSpecificErrorsList(
            self::$_fileTest,
            PhpDoc::CODE_PHP_DOC_UNKNOWN
        );

        $expected = 2;
        $this->assertCount($expected, $errors);
    }

    /**
     * Test extra gap
     */
    public function testPhpDocExtraGap()
    {
        $errors = $this->_getSpecificErrorsList(
            self::$_fileTest,
            PhpDoc::CODE_PHP_DOC_EXTRA_GAP
        );

        $expected = 3;
        $this->assertEquals($expected, $errors[0]);
    }

    /**
     * Test null in PHPDoc without extra types
     */
    public function testPhpDocVarNull()
    {
        $errors = $this->_getSpecificErrorsList(
            self::$_fileTest,
            PhpDoc::CODE_PHP_DOC_VAR_NULL
        );

        $expected = 3;
        $this->assertEquals($expected, $errors[0]);
    }

    /**
     * Test when @param hasn't described type
     */
    public function testParamEmptyType()
    {
        $errors = $this->_getSpecificErrorsList(
            self::$_fileTest,
            PhpDoc::CODE_PHP_DOC_VAR_EMPTY
        );

        $expected = 3;
        $this->assertEquals($expected, $errors[0]);
    }
}
