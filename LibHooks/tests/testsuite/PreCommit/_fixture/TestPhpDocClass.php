<?php
class Some_testClass extends stdClass //PhpDoc is missed
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
     * @var null
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
     * @var null
     */
    protected $_publicValue;

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
}
