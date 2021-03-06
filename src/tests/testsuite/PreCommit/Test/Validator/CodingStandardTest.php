<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
namespace PreCommit\Test\Validator;

use PreCommit\Config;
use PreCommit\Processor;
use PreCommit\Processor\ErrorCollector;
use PreCommit\Validator\CodingStandard;
use PreCommit\Vcs\Git;

/**
 * Class test for Processor
 */
class CodingStandardTest extends \PHPUnit_Framework_TestCase
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
        Config::mergeExtraConfig();

        $vcsAdapter = self::getVcsAdapterMock();

        /** @var Processor\PreCommit $processor */
        $processor = Processor::factory('pre-commit', ['vcs' => $vcsAdapter]);
        $processor->setCodePath(PROJECT_ROOT)
            ->setFiles([self::$classTest]);
        $processor->process();
        self::$model = $processor;
    }

    /**
     * Test CODE_PHP_OPERATOR_SPACES_MISSED
     */
    public function testOperatorSpaces()
    {
        $errors = $this->getSpecificErrorsList(
            self::$classTest,
            CodingStandard::CODE_PHP_OPERATOR_SPACES_MISSED
        );

        //TODO implement group comment validation
        $expected = [
            '$a =1;',
            '$a= 1;',
            '$a=1;',
            '$a = 1 +1;',
            '$a = 1+ 1;',
            '$a = 1+1;',
            '$a = 1 /1;',
            '$a = 1/ 1;',
            '$a = 1/1;',
            '$a = 1 *1;',
            '$a = 1* 1;',
            '$a = 1*1;',
            '$a = 1 -1;',
            '$a = 1- 1;',
            '$a = 1-1;',
            '$a = 1 &1;',
            '$a = 1& 1;',
            '$a = 1&1;',
            '$a = 1 %1;',
            '$a = 1% 1;',
            '$a = 1%1;',
            '$a = $a !=$a;',
            '$a = $a!= $a;',
            '$a = $a!=$a;',
            '$a = $a !==$a;',
            '$a = $a!== $a;',
            '$a = $a!==$a;',
            '$a = $a ==$a;',
            '$a = $a== $a;',
            '$a = $a==$a;',
            '$a = $a ===$a;',
            '$a = $a=== $a;',
            '$a = $a===$a;',
            'foreach (array() as $k=>$value) {',
//            'for ($i = 1; $i<2; $i++) {', //not implemented
            'for ($i=1; $i < 2; $i++) {',
            "'a' =>\$a,",
            "'a'=> \$a,",
            "'a'=>\$a,",
            "print_r('test',true);",
            "), -time()",
        ];
        $this->assertEquals($expected, array_values($errors));
    }

    /**
     * Test CODE_PHP_CONDITION_ASSIGNMENT
     */
    public function testAssignmentInCondition()
    {
        $errors   = $this->getSpecificErrorsList(
            self::$classTest,
            CodingStandard::CODE_PHP_CONDITION_ASSIGNMENT
        );
        $expected = [
            'if ($a = rand()) {',
        ];
        $this->assertEquals($expected, array_values($errors));
    }

    /**
     * Test CODE_PHP_REDUNDANT_SPACES
     */
    public function testRedundantSpaces()
    {
        $errors   = $this->getSpecificErrorsList(
            self::$classTest,
            CodingStandard::CODE_PHP_REDUNDANT_SPACES
        );
        $expected = [
            'public function test2Do( $param ) {',
            "print_r('test',  true);",
            "print_r('test' , true);",
            "print_r(rand(),  true);",
            "print_r(rand() , true);",
            'if ($a > rand())  {',
            'if ($a > rand() ) {',
            'if ( $a > rand()) {',
            'if  ($a > rand()) {',
            'rand( );',
            '$a   =    $a === $a;', //after = shouldn't more then 1 space
        ];
        $this->assertEquals($expected, array_values($errors));
    }

    /**
     * Test CODE_PHP_REDUNDANT_SPACES
     */
    public function testLineExceed()
    {
        $errors   = $this->getSpecificErrorsList(
            self::$classTest,
            CodingStandard::CODE_PHP_LINE_EXCEEDS,
            true
        );
        $expected = [81];
        $this->assertEquals($expected, $errors);
    }

    /**
     * Test CODE_PHP_SPACE_BRACKET
     */
    public function testSpaceBracket()
    {
        $errors   = $this->getSpecificErrorsList(
            self::$classTest,
            CodingStandard::CODE_PHP_SPACE_BRACKET
        );
        $expected = [
            'catch (Exception $e) {',
            '} catch (Exception $e) {$i = 1;}',
            'try { $i = 1; } catch (Exception $e) {$i = 1;}',
            '} catch(Exception $e) {',
            '}catch (Exception2 $e) {',
            '} catch (Exception3 $e){',
            '} catch (Exception4 $e)',
            'if ($a > rand())  {',
            'if ($a == 1)',
            'else',
            'if ($a == 1) echo 1; else echo 2;',
            'if ($a == 1) echo 1;',
            'else echo 2;',
            'if ($a == 1){',
            '}else {',
            'if($a == 1) {',
            '} else{',
            'if ($a == 1)',
            'else {',
            '} else',
            '} elseif ($a == 2){',
            '} elseif($a == 2) {',
            '}else if($a == 2) {',
            '}else if($a == 2)',
            'else if($a == 2)',
            'do{',
            '}while($a == 1);',
            'do',
            '} while($a == 1);',
            'while($a == 1) {',
            'while ($a == 1){',
            'while ($a == 1)',
        ];
        $this->assertEquals($expected, array_values($errors));
    }

    /**
     * Test CODE_PHP_SPACE_BRACKET
     */
    public function testFunctionNaming()
    {
        $errors   = $this->getSpecificErrorsList(
            self::$classTest,
            CodingStandard::CODE_PHP_PUBLIC_METHOD_NAMING_INVALID
        );
        $expected = [
            'public function _publicFunc()',
            'public function PublicFunc()',
        ];
        $this->assertEquals($expected, array_values($errors));
        $errors   = $this->getSpecificErrorsList(
            self::$classTest,
            CodingStandard::CODE_PHP_PROTECTED_METHOD_NAMING_INVALID
        );
        $expected = [
            'protected function protectedFunc()',
            'private function privateFunc()',
        ];
        $this->assertEquals($expected, array_values($errors));

        $errors   = $this->getSpecificErrorsList(
            self::$classTest,
            CodingStandard::CODE_PHP_METHOD_SCOPE
        );
        $expected = [
            'static function staticFunc()',
            'function func()',
        ];
        $this->assertEquals($expected, array_values($errors));
    }

    /**
     * Test gaps
     */
    public function testGaps()
    {
        $errors = $this->getSpecificErrorsList(
            self::$classTest,
            CodingStandard::CODE_PHP_GAPS
        );
        $this->assertEquals([1], $errors);
    }

    /**
     * Test gaps after/before opened/closed bracket/brace
     */
    public function testGapsAfterOrBeforeBracket()
    {
        $errors = $this->getSpecificErrorsList(
            self::$classTest,
            CodingStandard::CODE_PHP_BRACKET_GAPS
        );
        $this->assertEquals(
            [
                508 => 7,
                521 => 7,
                524 => 7,
                528 => 7,
                530 => 7,
                532 => 7,
                534 => 7,
            ],
            $errors
        );
    }

    /**
     * Test skipped public method naming
     */
    public function testSkipPublicMethodNaming()
    {
        $this->markTestIncomplete();
        $vcsAdapter = self::getVcsAdapterMock();

        /** @var Processor\PreCommit $processor */
        $processor = Processor::factory('pre-commit', $vcsAdapter);
        $processor->setCodePath(PROJECT_ROOT)
            ->setFiles(['testsuite/PreCommit/Test/_fixture/TestSkip.php']);
        $processor->process();

        $errors   = $this->getSpecificErrorsList(
            'testsuite/PreCommit/Test/_fixture/TestSkip.php',
            CodingStandard::CODE_PHP_PUBLIC_METHOD_NAMING_INVALID,
            false,
            $processor
        );
        $expected = ['public function _test2($param)'];
        $this->assertEquals($expected, array_values($errors));
    }

    /**
     * Test underscore in variable
     */
    public function testUnderscoreInVar()
    {
        $errors   = $this->getSpecificErrorsList(
            self::$classTest,
            CodingStandard::CODE_PHP_UNDERSCORE_IN_VAR
        );
        $expected = [
            '$_badA = 1;',
            '$_badB = 2;',
            '$bad_another = self::$_static + $_badA;',
            '$b = $bad_another;',
            'return $bad_another + $_badB;',
        ];
        $this->assertEquals($expected, array_values($errors));
    }

    /**
     * Test split simple content
     */
    public function testSplitContentSimple()
    {
        $content                   = '$this->getText(\'12345\');';
        $options['errorCollector'] = new ErrorCollector();
        $validator                 = new CodingStandard($options);
        $parsed                    = $validator->splitContent($content);

        $expected = '$this->getText(\'\');';
        $this->assertEquals($expected, current($parsed));
    }

    /**
     * Test split complex content
     */
    public function testSplitContentComplex()
    {
        $this->markTestIncomplete('Seem it should be simplified.');

        $content                   = file_get_contents(__DIR__.'/../_fixture/TestClassSplit.php');
        $options['errorCollector'] = new ErrorCollector();
        $validator                 = new CodingStandard($options);
        $parsed                    = $validator->splitContent($content);

        $expected = [
            '<?php',
            '',
            'class Some_testClass2',
            '{',
            '    ',
            '    protected function _render(array $data = array(), $template = null, $blockClass = null)',
            '    {',
            '        ',
            '        if ($blockClass) {',
            '            $blockClass = \'\' . \'\' . $blockClass;',
            '            $block = new $blockClass($data);',
            '        } else {',
            '            $block = new Renderer($data);',
            '        }',
            '',
            '        if (!$template) {',
            '            ',
            '            $template = $this->getControllerName() . DIRECTORY_SEPARATOR',
            '                . $this->getActionName() . \'\';',
            '        }',
            '        $block->setTemplate($template);',
            '',
            '        ',
            '        $actionHtml = $block->toHtml();',
            '',
            '        if (!$this->isAjax()) {',
            '            $block = new Renderer(array(\'\' => $actionHtml));',
            '            $block->setTemplate(\'\');',
            '',
            '            ',
            '            $message = new Message();',
            '            $message->setTemplate(\'\');',
            '            $block->setChild(\'\', $message);',
            '',
            '            echo $block->toHtml();',
            '        } else {',
            '            echo $actionHtml;',
            '        }',
            '        return $this;',
            '    }',
            '}',
            '',
        ];
        $this->assertEquals($expected, array_values($parsed));
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
            if ($key == 'value' && isset($item['line']) && isset($item['value'])) {
                if (is_array($item['line'])) {
                    foreach ($item['line'] as $line) {
                        $list[$line] = $item['value'];
                    }
                } else {
                    $list[$item['line']] = $item['value'];
                }
            } else {
                $list[] = $item[$key];
            }
        }

        return $list;
    }
}
