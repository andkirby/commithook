<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
namespace PreCommit\Test\Validator;

use PreCommit\Processor;
use PreCommit\Validator\PhpDoc;

/**
 * Class test for Processor
 */
class PhpDocTest extends PreCommitFlowTestCase
{
    /**
     * Set up test model
     */
    public static function setUpBeforeClass()
    {
        self::$fileTest = 'tests/testsuite/PreCommit/Test/_fixture/TestPhpDocClass.php';
        parent::setUpBeforeClass();
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
     * Test CODE_PHP_DOC_MISSED
     */
    public function testSimplePhpDocBlockMissed()
    {
        $errorCollector = new Processor\ErrorCollector();
        $validator      = new PhpDoc(['errorCollector' => $errorCollector]);

        $this->assertFalse($validator->validate($this->getTestFileContent(), static::$fileTest));

        $errors   = $this->getSpecificErrorsList(
            static::$fileTest,
            PhpDoc::CODE_PHP_DOC_MISSED,
            $errorCollector->getErrors()
        );
        $expected = [
            'const WRONG = 0; //PhpDoc is missed',
            'protected $_paramB; //PhpDoc is missed',
            'protected $_paramBB; //PhpDoc is missed',
            'public function test1() //PhpDoc is missed',
            'class Some_testClassPhpDoc extends stdClass //PhpDoc is missed',
        ];
        $this->assertEquals($expected, $errors);
    }

    /**
     * Test CODE_PHP_DOC_MESSAGE - missed PHPDoc or first letter in lower case
     */
    public function testPhpDocDescriptionMissed()
    {
        $errorCollector = new Processor\ErrorCollector();
        $validator      = new PhpDoc(['errorCollector' => $errorCollector]);

        $this->assertFalse($validator->validate($this->getTestFileContent(), static::$fileTest));

        $errors = $this->getSpecificErrorsList(
            static::$fileTest,
            PhpDoc::CODE_PHP_DOC_MESSAGE,
            true,
            $errorCollector
        );

        $expected = [57, 63, 68, 164, 173, 183, 235];
        $this->assertEquals($expected, array_shift($errors));
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
        $this->assertEquals($expected, array_shift($errors));
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
        $this->assertEquals($expected, array_shift($errors));
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
        $this->assertEquals($expected, array_shift($errors));
    }
}
