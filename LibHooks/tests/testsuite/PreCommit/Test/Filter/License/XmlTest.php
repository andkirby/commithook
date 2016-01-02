<?php
namespace PreCommit\Test\Filter\License;

use PreCommit\Filter\License\Xml;
use \PHPUnit_Framework_TestCase as TestCase;

/**
 * Class XmlTest
 * Testing inserting license block into XML content
 *
 * @package PreCommit\Test\Filter\License
 */
class XmlTest extends TestCase
{
    /**
     * Test inserting license
     */
    public function testSimpleInsertingLicense()
    {
        $generator = new Xml();
        $generator->setContent(
            '<?xml version="1.0"?>
<config>
    <node>Text</node>
</config>'
        );
        $generator->setLicense(
            '/**
 * License block
 */'
        );

        $expected
            = '<?xml version="1.0"?>
<!--
/**
 * License block
 */
-->
<config>
    <node>Text</node>
</config>';

        $this->assertEquals($expected, $generator->generate());
    }

    /**
     * Test exists license block
     */
    public function testExistsLicenseBlock()
    {
        $generator = new Xml();
        $generator->setContent(
            '<?xml version="1.0"?>
<!--
/**
 * License block
 */
-->
<config>
    <node>Text</node>
</config>'
        );
        $generator->setLicense(
            '/**
 * License block
 */'
        );

        $this->assertNull($generator->generate());
    }
}
