<?php
/**
 * Class test for PreCommit_Processor
 */
class PreCommit_ConfigTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test model
     *
     * @var \PreCommit\Config
     */
    static protected $_model;

    /**
     * Set up test model
     */
    static public function setUpBeforeClass()
    {
        $file         = __DIR__ . '/_fixture/config-test.xml';
        self::$_model = simplexml_load_file($file, '\\PreCommit\\Config');
    }

    /**
     * Test get node
     */
    public function testGetNode()
    {
        $result = self::$_model->getNode('node2/structure1/child');
        $this->assertEquals('value1', $result);
    }

    /**
     * Test get node
     */
    public function testGetNodeArray()
    {
        $result = self::$_model->getNodeArray('node2/structure1');
        $this->assertEquals(
            array('child' => 'value1', 'child2' => 'value2'),
            $result
        );
    }

    /**
     * Test get node
     */
    public function testGetMultiNode()
    {
        $result = self::$_model->getMultiNode('list/same', true, false);
        $this->assertEquals(
            array('same1', 'same2', 'same3', 'same4'),
            $result
        );
    }
}
