<?php

/**
 * Class test for PreCommit_Processor
 */
class PreCommit_Validator_CodingStandardTest extends PHPUnit_Framework_TestCase
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
     * @var \PreCommit\Processor\PreCommit
     */
    static protected $_model;

    /**
     * Set up test model
     */
    static public function setUpBeforeClass()
    {
        //init config object
        \PreCommit\Config::getInstance(array('file' => PROJECT_ROOT . '/commithook.xml'));

        $vcsAdapter = self::_getVcsAdapterMock();

        /** @var PreCommit\Processor\PreCommit $processor */
        $processor = PreCommit\Processor::factory('pre-commit', $vcsAdapter);
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
        $vcsAdapter = PHPUnit_Framework_MockObject_Generator::getMock('PreCommit\Vcs\Git');
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
     * @param object $model
     * @return array
     * @throws PHPUnit_Framework_Exception
     */
    protected function _getSpecificErrorsList($file, $code, $returnLines = false, $model = null)
    {
        if (!$model) {
            $model = self::$_model;
        }
        $errors = $model->getErrors();
        if (!isset($errors[$file])) {
            throw new PHPUnit_Framework_Exception('Errors for file ' . self::$_classTest . ' not found.');
        }
        $errors = $errors[$file];

        $this->assertArrayHasKey($code, $errors);
        if (!isset($errors[$code])) {
            throw new PHPUnit_Framework_Exception("Errors for code $code not found.");
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
     * Test CODE_PHP_OPERATOR_SPACES_MISSED
     */
    public function testOperatorSpaces()
    {
        $errors = $this->_getSpecificErrorsList(
            self::$_classTest,
            \PreCommit\Validator\CodingStandard::CODE_PHP_OPERATOR_SPACES_MISSED
        );

        //TODO implement group comment validation
        $expected = array (
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
        );
        $this->assertEquals($expected, array_values($errors));
    }

    /**
     * Test CODE_PHP_CONDITION_ASSIGNMENT
     */
    public function testAssignmentInCondition()
    {
        $errors = $this->_getSpecificErrorsList(
            self::$_classTest,
            \PreCommit\Validator\CodingStandard::CODE_PHP_CONDITION_ASSIGNMENT
        );
        $expected = array (
            'if ($a = rand()) {'
        );
        $this->assertEquals($expected, array_values($errors));
    }

    /**
     * Test CODE_PHP_REDUNDANT_SPACES
     */
    public function testRedundantSpaces()
    {
        $errors = $this->_getSpecificErrorsList(
            self::$_classTest,
            \PreCommit\Validator\CodingStandard::CODE_PHP_REDUNDANT_SPACES
        );
        $expected = array (
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
        );
        $this->assertEquals($expected, array_values($errors));
    }

    /**
     * Test CODE_PHP_REDUNDANT_SPACES
     */
    public function testLineExceed()
    {
        $errors = $this->_getSpecificErrorsList(
            self::$_classTest,
            \PreCommit\Validator\CodingStandard::CODE_PHP_LINE_EXCEEDS,
            true
        );
        $expected = array ('78');
        $this->assertEquals($expected, $errors);
    }

    /**
     * Test CODE_PHP_SPACE_BRACKET
     */
    public function testSpaceBracket()
    {
        $errors = $this->_getSpecificErrorsList(
            self::$_classTest,
            \PreCommit\Validator\CodingStandard::CODE_PHP_SPACE_BRACKET
        );
        $expected = array (
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
        );
        $this->assertEquals($expected, array_values($errors));
    }

    /**
     * Test CODE_PHP_SPACE_BRACKET
     */
    public function testFunctionNaming()
    {
        $errors = $this->_getSpecificErrorsList(
            self::$_classTest,
            \PreCommit\Validator\CodingStandard::CODE_PHP_PUBLIC_METHOD_NAMING_INVALID
        );
        $expected = array (
            'public function _publicFunc()',
            'public function PublicFunc()',
        );
        $this->assertEquals($expected, array_values($errors));
        $errors = $this->_getSpecificErrorsList(
            self::$_classTest,
            \PreCommit\Validator\CodingStandard::CODE_PHP_PROTECTED_METHOD_NAMING_INVALID
        );
        $expected = array (
            'protected function protectedFunc()',
            'private function privateFunc()',
        );
        $this->assertEquals($expected, array_values($errors));

        $errors = $this->_getSpecificErrorsList(
            self::$_classTest,
            \PreCommit\Validator\CodingStandard::CODE_PHP_METHOD_SCOPE
        );
        $expected = array (
            'static function staticFunc()',
            'function func()',
        );
        $this->assertEquals($expected, array_values($errors));
    }

    /**
     * Test gaps
     */
    public function testGaps()
    {
        $errors = $this->_getSpecificErrorsList(
            self::$_classTest,
            \PreCommit\Validator\CodingStandard::CODE_PHP_GAPS
        );
        $this->assertEquals(array(1), $errors);
    }

    /**
     * Test gaps after/before opened/closed bracket/brace
     */
    public function testGapsAfterOrBeforeBracket()
    {
        $errors = $this->_getSpecificErrorsList(
            self::$_classTest,
            \PreCommit\Validator\CodingStandard::CODE_PHP_BRACKET_GAPS
        );
        $this->assertEquals(array(7), $errors);
    }

    public function testConstructSpaces()
    {
//        $errors = self::$_model->getErrors();
//        qqq1($errors);
//        $this->assertEquals(array(7), $errors);
    }

    public function testSkipPublicMethodNaming()
    {
        $vcsAdapter = self::_getVcsAdapterMock();

        /** @var PreCommit\Processor\PreCommit $processor */
        $processor = PreCommit\Processor::factory('pre-commit', $vcsAdapter);
        $processor->setCodePath(PROJECT_ROOT)
            ->setFiles(array('tests/testsuite/PreCommit/_fixture/TestSkip.php'));
        $processor->process();

        $errors = $this->_getSpecificErrorsList(
            'tests/testsuite/PreCommit/_fixture/TestSkip.php',
            \PreCommit\Validator\CodingStandard::CODE_PHP_PUBLIC_METHOD_NAMING_INVALID,
            false,
            $processor
        );
        $expected = array('public function _test2($param)');
        $this->assertEquals($expected, array_values($errors));
    }

    /**
     * Test underscore in variable
     */
    public function testUnderscoreInVar()
    {
        $errors = $this->_getSpecificErrorsList(
            self::$_classTest,
            \PreCommit\Validator\CodingStandard::CODE_PHP_UNDERSCORE_IN_VAR
        );
        $expected = array(
            '$_badA = 1;',
            '$_badB = 2;',
            '$bad_another = self::$_static + $_badA;',
            '$b = $bad_another;',
            'return $bad_another + $_badB;',
        );
        $this->assertEquals($expected, array_values($errors));
    }
}
