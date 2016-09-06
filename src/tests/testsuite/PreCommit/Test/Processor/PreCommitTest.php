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
    public static function setUpBeforeClass()
    {
        //init config
        Config::initInstance(['file' => PROJECT_ROOT.'/config/root.xml']);
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
        return [
            [
                'php',
                [
                    'PhpClass'                   => 1,
                    'PhpDoc'                     => 1,
                    'CodingStandard'             => 1,
                    'RedundantCode'              => 1,
                    'Magento-MageExceptionThrow' => 1,
                    'Magento-ModelEventFields'   => 1,
                ],
            ],
            [
                'phtml',
                [
                    'RedundantCode'       => 1,
                    'CodingStandardPhtml' => 1,
                ],
            ],
            [
                'js',
                [
                    'RedundantCode' => 1,
                ],
            ],
            [
                'xml',
                [
                    'XmlParser' => 1,
                ],
            ],
        ];
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
        $test   = $this->getMock('\PreCommit\Processor\PreCommit', ['____'], [], '', false);
        $result = $test->getValidators($type);
        $this->assertEquals($expected, $result);
    }
}
