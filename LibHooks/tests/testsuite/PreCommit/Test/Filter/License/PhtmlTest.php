<?php
namespace PreCommit\Test\Filter\License;

use PreCommit\Filter\License\Phtml;
use \PHPUnit_Framework_TestCase as TestCase;

/**
 * Class PhtmlTest
 * Testing inserting license block into PHTML content
 *
 * @package PreCommit\Test\Filter\License
 */
class PhtmlTest extends TestCase
{
    /**
     * Test inserting license
     */
    public function testSimpleInsertingLicense()
    {
        $generator = new Phtml();
        $generator->setContent(
            '<?php
/** @var $this stdClass */
?>
<div>
    <span>Text</span>
</div>'
        );
        $generator->setLicense(
            '/**
 * License block
 */'
        );

        $expected
            = '<?php
/**
 * License block
 */
/** @var $this stdClass */
?>
<div>
    <span>Text</span>
</div>';

        $this->assertEquals($expected, $generator->generate());
    }

    /**
     * Test inserting license without PHP code block
     */
    public function testInsertingLicenseWithoutPhpBlock()
    {
        $generator = new Phtml();
        $generator->setContent(
            '<div>
    <span>Text</span>
</div>'
        );
        $generator->setLicense(
            '/**
 * License block
 */'
        );

        $expected
            = '<?php
/**
 * License block
 */
?>
<div>
    <span>Text</span>
</div>';

        $this->assertEquals($expected, $generator->generate());
    }

    /**
     * Test exists license block
     */
    public function testExistsLicenseBlock()
    {
        $generator = new Phtml();
        $generator->setContent(
            '<?php
/**
 * License block
 */
 ?>
 <div>
    <span>Text</span>
</div>'
        );
        $generator->setLicense(
            '/**
 * License block
 */'
        );

        $expected
            = '<?php
/**
 * License block
 */
?>
<div>
    <span>Text</span>
</div>';

        $this->assertNull($generator->generate());
    }
}
