<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */

namespace PreCommit\Test\Helper;

use PreCommit\Helper\PathMatch;

/**
 * Class PathMatchTest
 *
 * @package PreCommit\Test\Helper
 */
class PathMatchTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test simple match
     *
     * E.g.: test/11 should not match with test/1111
     */
    public function testSimpleMatch()
    {
        $match = new PathMatch();
        $match->setAllowed(
            array(
                'test/11/',
                'test/22/',
            )
        );
        $match->setProtected(
            array(
                'test/aa',
            )
        );

        $this->assertTrue($match->test('test/11/test.1'));
    }

    /**
     * Test simple match
     *
     * E.g.: test/11 should not match with test/1111
     */
    public function testMatchWithUnknownDirectory()
    {
        $match = new PathMatch();
        $match->setAllowed(
            array(
                'test/*/11/',
                'test/22/',
            )
        );

        $this->assertTrue($match->test('test/cc/11/test.1'));
    }

    /**
     * Test simple match
     *
     * E.g.: test/11 should not match with test/1111
     */
    public function testProtectedMatchWithUnknownDirectory()
    {
        $match = new PathMatch();
        $match->setProtected(
            array(
                'test/*/11/',
                'test/22/',
            )
        );

        $this->assertFalse($match->test('test/cc/11/test.1'));
    }

    /**
     * Test simple match
     *
     * E.g.: test/11 should not match with test/1111
     */
    public function testMatchWithUnknownDirectoriesStructure()
    {
        $match = new PathMatch();
        $match->setAllowed(
            array(
                'test/**/11/',
                'test/22/',
            )
        );

        $this->assertTrue($match->test('test/a/b/c/11/test.1'));
    }
}
