<?php

class Some_testClass extends stdClass {
    const WRONG = 0;

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
     * @var string  Here comment missing
     */
    protected $_param1;
    protected $_param2;

    /**
     * @var string
     */
    protected $_var;

    /**
     *
     * @var string
     */
    protected $_varA;

    /**
     * @var string      Here PHPDoc message missed
     */
    protected $_param3;

    /**
     * Static
     *
     * @var int
     */
    protected $_static = 1;

    /**
     * Protected name var without underscore
     *
     * @var null
     */
    protected $protectedValue;

    /**
     * Public name var with underscore. Missed gap in phpDoc
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

        //line exceeds 120
        $someVar = true;
        print_r($someVar, $someVar, $someVar, $someVar, $someVar, $someVar, $someVar, $someVar, $someVar, $someVar, $someVar);
    }

    /**
     * Test method without visibility name (NOT IMPLEMENTED)
     *
     * Test try catch.
     */
    public function testTryCatch()
    {
        try {
            //code
        }
        catch (Exception $e) {
            //code
        }

        try
        {
            //code
        } catch (Exception $e) {
            //code
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
        foreach (array() as $k=>$value) {
            //code
        }

        for ($i = 1; $i<2; $i++) {
            //code
        }

        for ($i=1; $i < 2; $i++) {
            //code
        }

        $arrA = array($a);
        foreach ($arrA as &$item) {
            //code
        }

        array(
            'a' =>$a,
            'a'=> $a,
            'a'=>$a,
        );

        print_r('test',true);

        $this->func(
            'save',
            array(
                 'label'     => 'value1',
                 'onclick'   => 'saveAndContinueEdit()',
                 'class'     => 'save',
            ), -100
        );

        $this->func(
            'save',
            array(
                 'label'     => 'value1',
                 'onclick'   => 'saveAndContinueEdit()',
                 'class'     => 'save',
            ), -time()
        );

        /**
         * Right code
         */
        for ($i = 1; $i < 2; $i++) {
            //code
        }
        $a->addHandler('productAttribute', Mage::helper('some_import/output'));
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
            //code
        }

        //redundant spaces
        print_r('test',  true);
        print_r('test' , true);
        print_r(rand(),  true);
        print_r(rand() , true);

        if ($a > rand())  {
            //code
        }
        if ($a > rand() ) {
            //code
        }
        if ( $a > rand()) {
            //code
        }
        if  ($a > rand()) {
            //code
        }

        rand( );

        $a   =    $a === $a;

        //Right code
        if ($a == rand()) {
            //code
        }

        $urlKey = $this->getProductModel()->formatUrlKey($a->getName());

        if (0 === stripos($a, substr($a, 0, -4))) {
            //code
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
            //code
        }while($a == 1);

        do
        {
            //code
        } while($a == 1);

        while($a == 1) {
            //code
        }
        while ($a == 1){
            //code
        }
        while ($a == 1)
        {
            //code
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

        if ($a == 1) {
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
        //code
    }

    /**
     * Public started with Capital letter
     */
    public function PublicFunc()
    {
        //code
    }

    /**
     * Protected without underscore
     */
    protected function protectedFunc()
    {
        //code
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
        //code
    }

    /**
     * Method without scope
     */
    function func()
    {
        //test redundant code
        $a = null;
        if (is_null($a)) {
            $a = 1;
        }
        $a = intval($a);
    }

    /**
     * Test name
     */
    public function __construct()
    {
        //code
    }

    /**
     * Underscore in vars
     */
    public function funcVarWithUnderscore()
    {
        $_badA = 1;
        $_badB = 2;
        $normA = 1;
        $normB = 2;
        $bad_another = self::$_static + $_badA;
        $a = self::$_static + $normA;
        $b = $bad_another;
        return $bad_another + $_badB;
    }

    /**
     * Check gaps in this method
     *
     * This method SHOULD be last
     */
    public function funcWithGaps()
    {

        $this->func();
        /**
         * In end added gap
         * This method should be last
         */
        $this->func();


        //double gap above
        $this->func();

        if (1 == 1) {

            //one gap after bracket
            $a = 1; //gap below

        }

        $a = array(

            'gap above and below'

        ); //gap below

    }

}

try {
    $shell = new stdClass();
    $shell->run();
} catch (My_Exception $e) {
    echo 'ERROR: ' . $e->getMessage() . PHP_EOL;
} catch (Exception $e) {
    echo $e . PHP_EOL;
}

$switch = $this->getRequest()->getGetParam('switch');
