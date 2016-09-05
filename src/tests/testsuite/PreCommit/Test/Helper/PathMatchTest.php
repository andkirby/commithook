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
     * Test default match
     *
     * No rules. Everything allowed
     */
    public function testDefaultMatch()
    {
        $match = new PathMatch();
        $this->assertFalse($match->test('test/11/test.1'));
    }

    /**
     * Test simple match
     *
     * E.g.: test/11 should not match with test/1111
     */
    public function testSimpleMatch()
    {
        $match = new PathMatch();
        $match->setAllowed(['test/11/', 'test/22/']);
        $this->assertTrue($match->test('test/11/test.1'));
    }

    /**
     * Test match allowed list within protected path
     *
     * E.g.: test/11 should not match with test/1111
     */
    public function testAllowedByDefault()
    {
        $match = new PathMatch();
        $match->setAllowedByDefault(true);

        $this->assertTrue($match->test('test/33333/test.1'));
    }

    /**
     * Test match allowed list within protected path
     *
     * E.g.: test/11 should not match with test/1111
     */
    public function testDisallowedByDefault()
    {
        $match = new PathMatch();
        $match->setAllowedByDefault(false);

        $this->assertFalse($match->test('test/33333/test.1'));
    }

    /**
     * Test match allowed list within protected path
     *
     * E.g.: test/11 should not match with test/1111
     */
    public function testTopProtectWithChildAllowed()
    {
        $match = new PathMatch();
        $match->setAllowedByDefault(true);

        $match->setAllowed(['test/11/', 'test/22/']);
        $match->setProtected(['test']);

        $this->assertTrue($match->test('test/11/test.1'));
    }

    /**
     * Test match allowed list within protected path
     *
     * E.g.: test/11 should not match with test/1111
     */
    public function testPushToProtectRoot()
    {
        $match = new PathMatch();
        $match->setAllowedByDefault(true);

        $match->setAllowed(['test/11/', 'test/22/']);
        $match->setProtected(['']);

        $this->assertTrue($match->test('test/11/test.1'));
        $this->assertFalse($match->test('test/33333/test.1'));
    }

    /**
     * Test match allowed list within protected path
     *
     * E.g.: test/11 should not match with test/1111
     */
    public function testPushToProtectSlashRoot()
    {
        $match = new PathMatch();
        $match->setAllowedByDefault(true);

        $match->setAllowed(['test/11/', 'test/22/']);
        $match->setProtected(['/']);

        $this->assertTrue($match->test('test/11/test.1'));
        $this->assertFalse($match->test('test/33333/test.1'));
    }

    /**
     * Test non-match allowed list within protected path
     *
     * E.g.: test/11 should not match with test/1111
     */
    public function testTopProtectWithNoChildAllowed()
    {
        $match = new PathMatch();
        $match->setAllowedByDefault(true);

        $match->setAllowed(['test/11', 'test/22']);
        $match->setProtected(['test']);

        $this->assertFalse($match->test('test/33333/test.1'));
    }

    /**
     * Test simple match
     *
     * E.g.: test/11 should not match with test/1111
     */
    public function testMatchWithUnknownDirectory()
    {
        $match = new PathMatch();
        $match->setAllowed(['test/*/11/', 'test/22/']);

        $this->assertTrue($match->test('test/cc/11/test.1'));
    }

    /**
     * Test started slash in rule
     *
     * E.g.: test/11 should not match with test/1111
     */
    public function testStartSlashInRule()
    {
        $match = new PathMatch();
        $match->setAllowed(['/test/11/']);
        $this->assertFalse($match->test('test/11/test.1'));
    }

    /**
     * Test started slash in rule
     *
     * E.g.: test/11 should not match with test/1111
     */
    public function testStartSlashInTestedValue()
    {
        $match = new PathMatch();
        $match->setAllowed(['test/11/']);
        $this->assertFalse($match->test('/test/11/test.1'));
    }

    /**
     * Test started slash in rule
     *
     * E.g.: test/11 should not match with test/1111
     */
    public function testStartSlashInBoth()
    {
        $match = new PathMatch();
        $match->setAllowed(['/test/11/']);
        $this->assertTrue($match->test('/test/11/test.1'));
    }

    /**
     * Test started slash in rule
     *
     * E.g.: test/11 should not match with test/1111
     */
    public function testPathWithSlash()
    {
        $match = new PathMatch();
        $match->setAllowed(['/test/11/']);
        $this->assertTrue($match->test('/test/11/foo/'));
    }

    /**
     * Test without slash in rule in the end
     */
    public function testPathWithoutSlash()
    {
        $match = new PathMatch();
        $match->setAllowed(['/test/11']);
        $this->assertTrue($match->test('/test/11/foo/'));
        $this->assertFalse($match->test('/test/1112313'));
    }

    /**
     * Test started slash in rule
     *
     * E.g.: test/11 should not match with test/1111
     */
    public function testPathWithoutSlashWithAsterisk()
    {
        $match = new PathMatch();
        $match->setAllowed(['/test/11*']);
        $this->assertTrue($match->test('/test/11'));
        $this->assertTrue($match->test('/test/11111'));
        $this->assertTrue($match->test('/test/11/foo/'));
        $this->assertTrue($match->test('/test/11aaa/foo/'));
        $this->assertTrue($match->test('/test/1112313'));
    }

    /**
     * Test simple match with asterisk (*)
     *
     * E.g.: test/11 should not match with test/1111
     */
    public function testProtectedMatchWithUnknownDirectory()
    {
        $match = new PathMatch();
        $match->setProtected(['test/*/11/', 'test/22/']);

        $this->assertFalse($match->test('test/cc/11/test.1'));
    }

    /**
     * Test simple match with recursive asterisk (**)
     *
     * E.g.: test/11 should not match with test/1111
     */
    public function testMatchWithUnknownDirectoriesStructure()
    {
        $match = new PathMatch();
        $match->setAllowed(
            [
                'test/**/11/',
                'test/22/',
            ]
        );

        $this->assertTrue($match->test('test/a/b/c/11/test.1'));
    }

    /**
     * Test simple match with recursive asterisk (**)
     *
     * E.g.: test/11 should not match with test/1111
     */
    public function testMatchAsteriskDirectoryNoSlash()
    {
        $match = new PathMatch();
        $match->setAllowed(
            [
                'test/*/11',
            ]
        );

        $this->assertTrue($match->test('test/a/11/test.1'));
        $this->assertTrue($match->test('test/a/11'));
        $this->assertFalse($match->test('test/a/11111'));
    }
}
