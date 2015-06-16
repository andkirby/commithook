<?php
namespace PreCommit\Filter;

/**
 * Test of interpreter of short commit message format
 *
 * @package PreCommit\Filter
 */
class ShortCommitMsgTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test filter commit message for single cache
     */
    public function testFilterGetCachedSummarySingle()
    {
        $this->markTestIncomplete('Due updated schema it is not complete.');
        $message = <<<MMM
I TEST-551
 - Some additional comment.
MMM;
        $expected = <<<MMM
Implemented TEST-551: Implement CmsDev module
 - Some additional comment.
MMM;

        /** @var \PHPUnit_Framework_MockObject_MockObject|ShortCommitMsg $test */
        $test = $this->getMock(
            __NAMESPACE__ . '\ShortCommitMsg',
            array('_getCacheDir', '_getIssue')
        );
        $test->expects($this->once())
            ->method('_getCacheDir')
            ->will($this->returnValue(__DIR__ . '/_fixture/'));

        $test->expects($this->never())
            ->method('_getIssue');

        $result = $test->filter($message);
        $this->assertEquals($result, $expected);
    }

    /**
     * Test filter commit message from file with plenty cached summaries
     *
     * @dataProvider dataManySummaries
     * @param string $message
     * @param string $expected
     */
    public function testFilterGetCachedSummaryMany($message, $expected)
    {
        $this->markTestIncomplete('Due updated schema it is not complete.');
        /** @var \PHPUnit_Framework_MockObject_MockObject|ShortCommitMsg $test */
        $test = $this->getMock(
            __NAMESPACE__ . '\ShortCommitMsg',
            array('_getCacheFile', '_getIssue')
        );
        $test->expects($this->once())
            ->method('_getCacheFile')
            ->will($this->returnValue(__DIR__ . '/_fixture/issues-test-v0_plenty'));
        $test->expects($this->never())
            ->method('_getIssue');

        $result = $test->filter($message);
        $this->assertEquals($result, $expected);
    }

    /**
     * Data provider of plenty summaries
     *
     * @return array
     */
    public function dataManySummaries()
    {
        return require __DIR__ . '/_fixture/issues-test-v0_plenty-data.php';
    }

    /**
     * Test skipping filter of commit message
     */
    public function testFilterSkipping()
    {
        $this->markTestIncomplete('Due updated schema it is not complete.');
        $expected = <<<MMM
Implemented TEST-551: Implement CmsDev module
 - Some additional comment.
MMM;
        /** @var \PHPUnit_Framework_MockObject_MockObject|ShortCommitMsg $test */
        $test = $this->getMock(
            __NAMESPACE__ . '\ShortCommitMsg',
            array('_getCacheFile', '_getIssue')
        );
        $test->expects($this->never())
            ->method('_getCacheFile');
        $test->expects($this->never())
            ->method('_getIssue');

        $result = $test->filter($expected);
        $this->assertEquals($result, $expected);
    }
}
