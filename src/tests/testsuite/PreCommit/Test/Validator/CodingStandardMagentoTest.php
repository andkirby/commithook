<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
namespace PreCommit\Test\Validator;

use PreCommit\Validator\CodingStandardMagento;

/**
 * Class test for Processor
 */
class CodingStandardMagentoTest extends CodingStandardTest
{
    /**
     * Test CODE_PHP_SPACE_BRACKET
     */
    public function testDeprecatedThrowException()
    {
        $errors = $this->_getSpecificErrorsList(
            self::$_classTest,
            CodingStandardMagento::CODE_PHP_DEPRECATED_THROW_EXCEPTION);
        $expected = array (
            "Mage::throwException('text');",
        );
        $this->assertEquals($expected, array_values($errors));
    }
}
