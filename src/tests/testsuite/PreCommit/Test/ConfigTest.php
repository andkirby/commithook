<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
namespace PreCommit\Test;

/**
 * Class test for Processor
 */
class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test model
     *
     * @var \PreCommit\Config
     */
    static protected $model;

    /**
     * Set up test model
     */
    public static function setUpBeforeClass()
    {
        $file        = __DIR__.'/_fixture/config-test.xml';
        self::$model = simplexml_load_file($file, '\\PreCommit\\Config');
    }

    /**
     * Test get node
     */
    public function testGetNode()
    {
        $result = self::$model->getNode('node2/structure1/child');
        $this->assertEquals('value1', $result);
    }

    /**
     * Test get node
     */
    public function testGetNodeArray()
    {
        $result = self::$model->getNodeArray('node2/structure1');
        $this->assertEquals(
            ['child' => 'value1', 'child2' => 'value2'],
            $result
        );
    }

    /**
     * Test get node
     */
    public function testGetMultiNode()
    {
        $result = self::$model->getMultiNode('list/same', true, false);
        $this->assertEquals(
            ['same1', 'same2', 'same3', 'same4'],
            $result
        );
    }
}
