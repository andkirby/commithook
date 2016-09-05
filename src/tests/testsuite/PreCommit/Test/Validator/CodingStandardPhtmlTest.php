<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
namespace PreCommit\Test\Validator;

use PreCommit\Config;
use PreCommit\Processor;
use PreCommit\Validator\CodingStandardPhtml;

/**
 * Class test for Processor
 *
 * @todo Implement validation PHP code within PHP tags
 */
class CodingStandardPhtmlTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Php file for text hooks
     *
     * @var string
     */
    static protected $fileTest = 'tests/testsuite/PreCommit/Test/_fixture/test-standard-phtml.phtml';

    /**
     * Test model
     *
     * @var Processor\PreCommit
     */
    static protected $model;

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
     * Test CODE_PHTML_ALTERNATIVE_SYNTAX
     */
    public function testNotUsedAlternativeSyntax()
    {
        $errors   = $this->getSpecificErrorsList(
            self::$fileTest,
            CodingStandardPhtml::CODE_PHTML_ALTERNATIVE_SYNTAX
        );
        $expected = [
            '<?php foreach ($this->getData(\'array\') as $_value) { ?>',
            '<?php if ($this->getData(\'aa\') === \'some string\') { ?>',
            '<?php if ($i != $recommendationsCount-1) echo ","?>',
        ];
        $this->assertEquals($expected, array_values($errors));
    }

    /**
     * Test CODE_PHTML_UNDERSCORE_IN_VAR
     */
    public function testUnderscoreInVar()
    {
        $errors   = $this->getSpecificErrorsList(
            self::$fileTest,
            CodingStandardPhtml::CODE_PHTML_UNDERSCORE_IN_VAR
        );
        $expected = [
            '$_myVar = $this->getSomeData();',
            '<?php foreach ($this->getData(\'array\') as $_value) { ?>',
            '<?php echo $_value; ?>',
            '<?php echo $_myVar; ?>',
            '<?php $_product = $this->getProduct(); ?>',
        ];
        $this->assertEquals($expected, array_values($errors));
    }

    /**
     * Test CODE_PHTML_UNDERSCORE_IN_VAR
     */
    public function testProtectedMethodUsage()
    {
        $errors   = $this->getSpecificErrorsList(
            self::$fileTest,
            CodingStandardPhtml::CODE_PHTML_PROTECTED_METHOD
        );
        $expected = [
            '$myProtected = $this->_getProtectedData();',
        ];
        $this->assertEquals($expected, array_values($errors));
    }

    /**
     * Test CODE_PHTML_GAPS
     */
    public function testGaps()
    {
        $errors   = $this->getSpecificErrorsList(
            self::$fileTest,
            CodingStandardPhtml::CODE_PHTML_GAPS
        );
        $expected = [3];
        $this->assertEquals($expected, array_values($errors));
    }

    /**
     * Test CODE_PHTML_GAPS
     */
    public function testClassUsage()
    {
        $errors   = $this->getSpecificErrorsList(
            self::$fileTest,
            CodingStandardPhtml::CODE_PHTML_CLASS
        );
        $expected = [
            '<?php echo SomeClass::someMethod($value); ?>',
            '<?php echo Mage::helper($value); ?>',
            '<?php Mage::getModel(\'catalog/product\'); ?>',
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
            throw new \PHPUnit_Framework_Exception('Errors for file '.self::$fileTest.' not found.');
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
