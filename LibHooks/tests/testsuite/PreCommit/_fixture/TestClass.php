<?php

class Some_testClass extends stdClass
{
    const WRONG = 0;

    /**
     * Constant
     */
    const RIGHT = 0;

    /**
     * Good PHPDoc
     *
     * @var string  Here comment missing
     */
    protected $_param1;
    protected $_param2;

    /**
     * @var string      Here PHPDoc message missed
     */
    protected $_param3;

    public function test1(){
        //test of missing phpDoc and wrong place of brace and no needed space
    }

    /**
     * Enter description here...
     *
     * @param unknown_type $param
     */
    public function test2Do( $param ) {
        //Test wrong place of brace and unneeded space between brackets
    }

    /**
     * Test method without visibility name (NOT IMPLEMENTED)
     *
     * Test try catch.
     */
    public function testTryCatch()
    {
        try {

        }
        catch (Exception $e) {

        }

        try
        {

        } catch (Exception $e) {

        }

        try {
            $i = 1;
        } catch (Exception $e) {$i = 1;}

        try { $i = 1; } catch (Exception $e) {$i = 1;}

        try {
            $i = 1;
        } catch(Exception $e) {
        }catch (Exception2 $e) {
        } catch (Exception3 $e){
        } catch (Exception4$e) {
        } catch (Exception4 $e)
        {
            $i = 1;
        }
    }

    /**
     * Test spaces for <>=.-+&%*
     */
    public function testOperatorSpaces()
    {
        $a =1;
        $a= 1;
        $a=1;
        $a = 1 +1;
        $a = 1+ 1;
        $a = 1+1;
        $a = 1 -1;
        $a = 1- 1;
        $a = 1-1;
        $a = 1 /1;
        $a = 1/ 1;
        $a = 1/1;
        $a = 1 *1;
        $a = 1* 1;
        $a = 1*1;
        $a = 1 &1;
        $a = 1& 1;
        $a = 1&1;

        for ($i = 1; $i<2; $i++) {
            //code
        }

        for ($i=1; $i < 2; $i++) {
            //code
        }

        print_r('some',true);

        /**
         * Right code
         */
        for ($i = 1; $i < 2; $i++) {
            //code
        }

        print_r('some', true);

        $a = 1;
        $a = 1 + 1;
        $a = 1 - 1;
        $a = 1 / 1;
        $a = 1 * 1;
        $a = 1 & 1;
        $a++;
        $a--;
        ++$a;
        --$a;
        $i = ++$a;
        $i = --$a;
        $arr[$a++] = 1;
        $arr[$a--] = 1;
        $arr[++$a] = 1;
        $arr[--$a] = 1;
    }

    /**
     * @var string      Here PHPDoc message missed
     */
    public function someFuncWithoutMessage()
    {

    }
}
