<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
namespace PreCommit\Test\Processor;

use PreCommit\Config;

/**
 * Class test for Processor
 */
class PreCommitTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Set up test model
     */
    static public function setUpBeforeClass()
    {
        //init config
        Config::initInstance(array('file' => PROJECT_ROOT . '/config.xml'));
        Config::setSrcRootDir(PROJECT_ROOT);
        Config::mergeExtraConfig();
    }

    /**
     * Data provider for test testGetValidatorsType()
     *
     * @return array
     */
    public function dataTestValidatorsType()
    {
        return array(
            array(
                'php',
                array(
                    'PhpClass' => 1,
                    'PhpDoc' => 1,
                    'CodingStandard' => 1,
                    'RedundantCode' => 1,
                    'CodingStandardMagento' => 1,
                )
            ),
            array(
                'phtml',
                array(
                    'RedundantCode' => 1,
                    'CodingStandardPhtml' => 1,
                )
            ),
            array(
                'js',
                array(
                    'RedundantCode' => 1,
                )
            ),
            array(
                'xml',
                array(
                    'XmlParser' => 1,
                )
            ),
        );
    }

    /**
     * Test get validators
     *
     * @dataProvider dataTestValidatorsType
     * @param string $type
     * @param array  $expected
     */
    public function testGetValidatorsType($type, $expected)
    {
        /** @var \PreCommit\Processor\PreCommit $test */
        $test = $this->getMock('\PreCommit\Processor\PreCommit', array('____'), array(), '', false);
        $result = $test->getValidators($type);
        $this->assertEquals($expected, $result);
    }
}
