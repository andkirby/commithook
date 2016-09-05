<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
namespace PreCommit\Test\Validator\Magento;

use PreCommit\Test\Validator\CodingStandardTest;
use PreCommit\Validator\Magento\MageExceptionThrow;

/**
 * Class test for Processor
 */
class MageExceptionThrowTest extends CodingStandardTest
{
    /**
     * Test CODE_PHP_SPACE_BRACKET
     */
    public function testDeprecatedThrowException()
    {
        $errors   = $this->getSpecificErrorsList(
            self::$_classTest,
            MageExceptionThrow::CODE_PHP_DEPRECATED_THROW_EXCEPTION
        );
        $expected = [
            "Mage::throwException('text');",
        ];
        $this->assertEquals($expected, array_values($errors));
    }
}
