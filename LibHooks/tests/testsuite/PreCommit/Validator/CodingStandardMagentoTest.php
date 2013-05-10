<?php

/**
 * Class test for PreCommit_Processor
 */
class PreCommit_Validator_CodingStandardMagentoTest extends PreCommit_Validator_CodingStandardTest
{
    /**
     * Test CODE_PHP_SPACE_BRACKET
     */
    public function testDeprecatedThrowException()
    {
        $errors = $this->_getSpecificErrorsList(
            self::$_classTest,
            \PreCommit\Validator\CodingStandardMagento::CODE_PHP_DEPRECATED_THROW_EXCEPTION);
        $expected = array (
            "Mage::throwException('text');",
        );
        $this->assertEquals($expected, array_values($errors));
    }
}
