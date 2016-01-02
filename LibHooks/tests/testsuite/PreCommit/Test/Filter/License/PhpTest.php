<?php
namespace PreCommit\Test\Filter\License;

use PHPUnit_Framework_TestCase as TestCase;
use PreCommit\Filter\License\Php;

/**
 * Class PhpTest
 * Testing inserting license block into Php content
 *
 * @package PreCommit\Test\Filter\License
 */
class PhpTest extends TestCase
{
    /**
     * Test inserting license
     */
    public function testSimpleInsertingLicense()
    {
        $generator = new Php();
        //@startSkipCommitHooks
        $generator->setContent(
            '<?php
/**
 * @var $this stdClass
 */
$a = 123;
'
        );
        //@finishSkipCommitHooks
        $generator->setLicense(
            '/**
 * License block
 */'
        );

        //@startSkipCommitHooks
        $expected
            = '<?php
/**
 * License block
 */

/**
 * @var $this stdClass
 */
$a = 123;
';
        //@finishSkipCommitHooks

        $this->assertEquals($expected, $generator->generate());
    }
}
