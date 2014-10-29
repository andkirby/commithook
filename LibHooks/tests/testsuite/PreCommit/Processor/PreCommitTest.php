<?php
/**
 * Class test for PreCommit_Processor
 */
class PreCommit_Processor_PreCommitTest extends PHPUnit_Framework_TestCase
{
    /**
     * Set up test model
     */
    static public function setUpBeforeClass()
    {
        //init config
        \PreCommit\Config::getInstance(array('file' => PROJECT_ROOT . '/config.xml'));
        \PreCommit\Config::mergeExtraConfig(PROJECT_ROOT, 'd:/hook');
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
                    'PhpClass',
                    'PhpDoc',
                    'CodingStandard',
                    'RedundantCode',
                    'CodingStandardMagento',
                )
            ),
            array(
                'phtml',
                array(
                    'RedundantCode',
                    'CodingStandardPhtml',
                )
            ),
            array(
                'js',
                array(
                    'RedundantCode',
                )
            ),
            array(
                'xml',
                array(
                    'XmlParser',
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
