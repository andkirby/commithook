<?php
namespace testsuite\PreCommit\Test\Filter\ShortCommitMsg;

use PreCommit\Filter\ShortCommitMsg;
use PreCommit\Issue;
use PreCommit\Message;

/**
 * Class FormatterTest
 *
 * @package testsuite\PreCommit\Test\Filter\ShortCommitMsg
 */
class FormatterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test formatting full commit message
     */
    public function testMessageBodyFormatting()
    {
        $message            = new Message();
        $message->body      = '234 some test';
        $message->issueKey  = 'TEST-234';
        $message->summary   = 'My summary!';
        $message->userBody  = "line 1\n line 2";
        $message->verb      = 'SomeVerb';

        /** @var ShortCommitMsg\Formatter|\PHPUnit_Framework_MockObject_MockObject $formatter */
        $formatter = new ShortCommitMsg\Formatter();
        $this->assertEquals($message, $formatter->filter($message));
        $this->assertEquals($message->body, "SomeVerb TEST-234: My summary!\nline 1\n line 2");
        $this->assertEquals($message->head, "SomeVerb TEST-234: My summary!");
    }

    /**
     * Get issue mock
     *
     * @param string $summary
     * @param string $key
     * @param string $type
     * @param string $originalType
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getIssueMock($summary, $key, $type, $originalType)
    {
        /** @var Issue\AdapterAbstract|\PHPUnit_Framework_MockObject_MockObject $filter */
        $issue = $this->getMock('PreCommit\Issue\AdapterAbstract', array(), array(), '', false);
        $issue->method('getSummary')
            ->willReturn($summary);
        $issue->method('getKey')
            ->willReturn($key);
        $issue->method('getType')
            ->willReturn($type);
        $issue->method('getOriginalType')
            ->willReturn($originalType);
        return $issue;
    }
}
