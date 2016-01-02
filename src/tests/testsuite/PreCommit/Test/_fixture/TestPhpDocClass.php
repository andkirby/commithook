<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
class Some_testClassPhpDoc extends stdClass //PhpDoc is missed
{
    const WRONG = 0; //PhpDoc is missed

    /**
     * Constant
     */
    const RIGHT = 0;

    /**#@+
     * Group comment
     */
    const NODE_1   = 'node';
    const NODE_2   = 'node';

    const NODE_3   = 'node';
    /**#@-*/

    /**
     * Good PHPDoc
     *
     * @var string
     */
    protected $_paramA;
    protected $_paramB; //PhpDoc is missed

    protected $_paramBB; //PhpDoc is missed

    /**
     * Some comment
     *
     * @var null
     */
    protected $_varNull; //extra type missed in PHPDoc for @var

    /**
     * Some comment
     *
     * @var NULL
     */
    protected $_varNullUpper; //extra type missed in PHPDoc for @var

    /**
     * Some comment
     *
     * @var null|string
     * @var string|null
     */
    protected $_varProperNull; //extra type NOT missed in PHPDoc for @var

    /**
     * @var string
     */
    protected $_var; //comment missed in PHPDoc

    /**
     *
     * @var string
     */
    protected $_varA; //comment missed in PHPDoc

    /**
     * @var string      Here PHPDoc message missed
     */
    protected $_paramC;

    /**
     * Static
     *
     * @var int
     */
    protected $_static = 1; //Test static name

    /**
     * Enter description here...
     *
     * @var string
     */
    protected $_protectedValueA;

    /**
     * Invalid unknown type
     *
     * @var unknown_type
     */
    protected $_protectedValueB;

    /**
     * Missed gap in phpDoc
     * @var string
     */
    protected $_publicValue;

    /**
     * Empty var tag
     *
     * @var
     */
    protected $_emptyVar;

    public function test1() //PhpDoc is missed
    {
        //test of missing phpDoc
    }

    /**
     * Test unknown_type in PHPDoc of method
     *
     * @param unknown_type $param
     */
    public function test2Do($param)
    {
        //Test unknown_type in PHPDoc
    }

    /**
     * Test null in PHPDoc of method
     *
     * @param null $param
     */
    public function test3ParamNull($param)
    {
        //Test null in PHPDoc
    }

    /**
     * Test proper null in PHPDoc of method
     *
     * @param null|string $param
     * @param string|null $another
     * @param string|int|null $another
     * @param null|string|int $another
     */
    public function test3ProperParamNull($param, $another)
    {
        //Test null in PHPDoc
    }

    /**
     * Enter description here...
     *
     * @param int $param
     */
    public function test3Do($param)
    {
        //Test Enter description here... in PHPDoc
    }

    /**
     * Missed gap in phpDoc 2
     * @param int $param
     */
    public function test4Do($param)
    {
        //Test
    }

    /**
     * @param int $param
     */
    public function test5Do($param)
    {
        //Test missed description in PHPDoc
    }

    /**
     *
     * @param int $param
     */
    public function test6Do($param)
    {
        //Test missed description in PHPDoc
    }

    /**
     * lowercase name
     *
     * @param int $param
     */
    public function testLowercase($param)
    {
        //Test Right PHPDoc
    }

    /**
     * Right method
     *
     * @param int $param
     */
    public function testRight($param)
    {
        //Test Right PHPDoc
    }

    /**
     * Test extra gap 1
     *
     *
     * @param int $param
     */
    public function testExtraGap1($param)
    {
        //Test Right PHPDoc
    }

    /**
     * Test extra gap 2
     *
     */
    public function testExtraGap2($param)
    {
        //Test wrong PHPDoc
    }

    /**
     * Test extra gap 3
     *
     * @param int $param
     *
     */
    public function testExtraGap3($param)
    {
        //Test wrong PHPDoc
    }

    /**
     *
     * Test extra gap 4
     *
     * @param int $param
     */
    public function testExtraGap4($param)
    {
        //Test wrong PHPDoc
        // !!! For this case will responsible validator CODE_PHP_DOC_MESSAGE
    }

    /**
     * Some message
     *
     * @param $param1
     * @param
     */
    protected function _testTypeParam($param1, $param2)
    {
        //empty
    }
}
