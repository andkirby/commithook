<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
namespace PreCommit\Test\Validator;

use PreCommit\Config;
use PreCommit\Processor;
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
    protected static $fileTest = 'tests/testsuite/PreCommit/Test/_fixture/TestPhpDocClass.php';

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
        Config::initInstance(['file' => PROJECT_ROOT.'/commithook.xml']);
        Config::setSrcRootDir(PROJECT_ROOT);
        $vcsAdapter = self::getVcsAdapterMock();

        /** @var Processor\PreCommit $processor */
        $processor = Processor::factory('pre-commit', $vcsAdapter);
        $processor->setCodePath(PROJECT_ROOT)
            ->setFiles([self::$fileTest]);
        $processor->process();
        self::$model = $processor;
    }

    /**
     * Test CODE_PHP_DOC_MISSED
     */
    public function testPhpDocBlockMissed()
    {
        $errors = $this->getSpecificErrorsList(
            self::$fileTest,
            PhpDoc::CODE_PHP_DOC_MISSED
        );

        $expected = [
            'const WRONG = 0; //PhpDoc is missed',
            'protected $_paramB; //PhpDoc is missed',
            'protected $_paramBB; //PhpDoc is missed',
            'public function test1() //PhpDoc is missed',
            'class Some_testClassPhpDoc extends stdClass //PhpDoc is missed',
        ];
        $this->assertEquals($expected, array_values($errors));
    }

    /**
     * Test CODE_PHP_DOC_MESSAGE
     */
    public function testPhpDocDescriptionMissed()
    {
        $errors = $this->getSpecificErrorsList(
            self::$fileTest,
            PhpDoc::CODE_PHP_DOC_MESSAGE
        );

        // @codingStandardsIgnoreStart
        $expected = [
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
        ];
        // @codingStandardsIgnoreEnd

        $this->assertEquals($expected, array_values($errors));
    }

    /**
     * Test CODE_PHP_DOC_MISSED_GAP
     */
    public function testPhpDocMissedGap()
    {
        $errors = $this->getSpecificErrorsList(
            self::$fileTest,
            PhpDoc::CODE_PHP_DOC_MISSED_GAP
        );

        $expected = [
            '     * Missed gap in phpDoc',
            '     * Missed gap in phpDoc 2',
        ];
        $this->assertEquals($expected, array_values($errors));
    }

    /**
     * Test CODE_PHP_DOC_ENTER_DESCRIPTION
     */
    public function testPhpDocEnterDescription()
    {
        $errors = $this->getSpecificErrorsList(
            self::$fileTest,
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
        $errors = $this->getSpecificErrorsList(
            self::$fileTest,
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
        $errors = $this->getSpecificErrorsList(
            self::$fileTest,
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
        $errors = $this->getSpecificErrorsList(
            self::$fileTest,
            PhpDoc::CODE_PHP_DOC_VAR_NULL
        );

        $expected = 3;
        $this->assertEquals($expected, $errors[0]);
    }

    /**
     * Test when @param hasn 't described type
     */
    public function testParamEmptyType()
    {
        $errors = $this->getSpecificErrorsList(
            self::$fileTest,
            PhpDoc::CODE_PHP_DOC_VAR_EMPTY
        );

        $expected = 3;
        $this->assertEquals($expected, $errors[0]);
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
            throw new \PHPUnit_Framework_Exception('Errors for file '.self::$fileTest.' not found.');
        }
        $errors = $errors[$file];

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
