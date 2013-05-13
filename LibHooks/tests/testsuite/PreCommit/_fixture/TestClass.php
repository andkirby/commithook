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

    /**
     * Protected name var without underscore
     *
     * @var null
     */
    protected $protectedValue;

    /**
     * Public name var with underscore
     *
     * @var null
     */
    public $_publicValue;

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
        $a = 1 /1;
        $a = 1/ 1;
        $a = 1/1;
        $a = 1 *1;
        $a = 1* 1;
        $a = 1*1;
        $a = 1 -1;
        $a = 1- 1;
        $a = 1-1;
        $a = 1 &1;
        $a = 1& 1;
        $a = 1&1;
        $a = 1 %1;
        $a = 1% 1;
        $a = 1%1;
        $a = $a !=$a;
        $a = $a!= $a;
        $a = $a!=$a;
        $a = $a !==$a;
        $a = $a!== $a;
        $a = $a!==$a;
        $a = $a ==$a;
        $a = $a== $a;
        $a = $a==$a;
        $a = $a ===$a;
        $a = $a=== $a;
        $a = $a===$a;

        for ($i = 1; $i<2; $i++) {
            //code
        }

        for ($i=1; $i < 2; $i++) {
            //code
        }

        array(
            'a' =>$a,
            'a'=> $a,
            'a'=>$a,
        );

        print_r('test',true);

        /**
         * Right code
         */
        for ($i = 1; $i < 2; $i++) {
            //code
        }
        $a->addHandler('productAttribute', Mage::helper('onepica_import/output'));
        print_r('test', true);

        array(
            'a' => $a,
        );

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
        //assignment in a condition
        if ($a = rand()) {

        }

        //redundant spaces
        print_r('test',  true);
        print_r('test' , true);
        print_r(rand(),  true);
        print_r(rand() , true);

        if ($a > rand())  {

        }
        if ($a > rand() ) {

        }
        if ( $a > rand()) {

        }
        if  ($a > rand()) {

        }

        rand( );

        //line exceeds 120
        $someVar = true;
        print_r($someVar, $someVar, $someVar, $someVar, $someVar, $someVar, $someVar, $someVar, $someVar, $someVar, $someVar);

        $a   =    $a === $a;

        //Right code
        if ($a == rand()) {

        }

        $urlKey = $this->getProductModel()->formatUrlKey($a->getName());

        if (0 === stripos($a, substr($a, 0, -4))) {

        }

        rand();

        array(
            'a'   =>     $a,
        );

        $s = '';
        print_r($someVar, $someVar, $someVar, $someVar, $someVar, $someVar, $someVar, $someVar, $someVar, $someVar, $s);
        print_r('long_string long_string long_string long_string long_string long_string long_string long_string long_string', true);
    }

    /**
     * Test
     */
    public function testIfDoWhileEtc()
    {
        $a = 1;
        if ($a == 1)
            echo 1;
        else
            echo 2;

        if ($a == 1) echo 1; else echo 2;

        if ($a == 1) echo 1;
        else echo 2;

        if ($a == 1){
            echo 1;
        }else {
            echo 2;
        }

        if($a == 1) {
            echo 1;
        } else{
            echo 2;
        }

        if ($a == 1)
        {
            echo 1;
        }

        if ($a == 1) {
            echo 1;
        }
        else {
            echo 2;
        }

        if ($a == 1) {
            echo 1;
        } else
        {
            echo 2;
        }

        if ($a == 1) {
            echo 1;
        } elseif ($a == 2){
        } elseif($a == 2) {
        }else if($a == 2) {
        }else if($a == 2)
        {
        }
        else if($a == 2)
        {
            echo 2;
        }

        do{

        }while($a == 1);

        do
        {

        } while($a == 1);

        while($a == 1) {

        }
        while ($a == 1){

        }
        while ($a == 1)
        {

        }

        //Right code
        if ($a == 1) {
            echo 1;
        } elseif ($a == 2) {
            echo 3;
        } else {
            echo 2;
        }

        if ($a == 1
            || $a == 2
            && $a == 3
        ) {
            echo 2;
        }

        $a = new stdClass();
        $a->doTrackEcommerceOrder(
            $a->getIncrementId()
        );

        $a->doTrackdoOrder($a->getIncrementIdDo());

        do {
            echo 2;
        } while ($a == 1);
    }

    /**
     * Test mage standards
     */
    public function mageStandarts()
    {
        Mage::throwException('text');
    }

    /**
     * Public with underscore
     */
    public function _publicFunc()
    {

    }

    /**
     * Public started with Capital letter
     */
    public function PublicFunc()
    {

    }

    /**
     * Protected without underscore
     */
    protected function protectedFunc()
    {

    }

    /**
     * Private function test
     */
    private function privateFunc()
    {
        //some function test
        /**
         * some function test
         */

        /*
         * some function test
         */

        /*
         some function test
         */
    }

    /**
     * Method without scope
     */
    static function staticFunc()
    {

    }

    /**
     * Method without scope
     */
    function func()
    {

    }
}
