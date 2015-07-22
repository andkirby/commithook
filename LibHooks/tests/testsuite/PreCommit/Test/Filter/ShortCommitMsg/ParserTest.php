<?php
namespace testsuite\PreCommit\Test\Filter\ShortCommitMsg;

use PreCommit\Filter\ShortCommitMsg;
use PreCommit\Issue;
use PreCommit\Message;

/**
 * Class ParserTest
 *
 * @package testsuite\PreCommit\Test\Filter\ShortCommitMsg
 */
class ParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test simple short message
     */
    public function testFilterShortMessageWithoutVerb()
    {
        $summary       = 'Test summary!!!';
        $issueKey      = 'TEST-123';
        $key           = '123';
        $type          = 'task';
        $originalType  = 'Task';
        $userBody      = "My test 1!\nTest 2.";
        $commitMessage = $key . ' ' . $userBody;

        $message       = new Message();
        $message->body = $commitMessage;

        $issue = $this->_getIssueMock($summary, $key, $type, $originalType);

        /** @var ShortCommitMsg\Parser|\PHPUnit_Framework_MockObject_MockObject $parser */
        $parser = $this->getMock('PreCommit\Filter\ShortCommitMsg\Parser', array('_initIssue', '_getIssue'));
        $parser->method('_initIssue')
            ->willReturnSelf();
        $parser->method('_getIssue')
            ->willReturn($issue);

        $result = $parser->interpret($message);

        $this->assertEquals($message, $result);
        $this->assertEquals($issue, $message->issue);
        $this->assertEquals($summary, $message->summary);
        $this->assertEquals($issueKey, $message->issueKey);
        $this->assertEquals($userBody, $message->userBody);
        $this->assertEquals('Implemented', $message->verb);
        $this->assertEquals('', $message->shortVerb);
    }

    /**
     * Test simple short message with short verb
     */
    public function testFilterShortMessageVerb()
    {
        $summary       = 'Test summary!!!';
        $issueKey      = 'TEST-123';
        $key           = '123';
        $type          = 'task';
        $originalType  = 'Task';
        $userBody      = "My test 1!\nTest 2.";
        $commitMessage = "I $key\n$userBody";

        $message       = new Message();
        $message->body = $commitMessage;

        $issue = $this->_getIssueMock($summary, $key, $type, $originalType);

        /** @var ShortCommitMsg\Parser|\PHPUnit_Framework_MockObject_MockObject $parser */
        $parser = $this->getMock('PreCommit\Filter\ShortCommitMsg\Parser', array('_initIssue', '_getIssue'));
        $parser->method('_initIssue')
            ->willReturnSelf();
        $parser->method('_getIssue')
            ->willReturn($issue);

        $result = $parser->interpret($message);

        $this->assertEquals($message, $result);
        $this->assertEquals($issue, $message->issue);
        $this->assertEquals($summary, $message->summary);
        $this->assertEquals($issueKey, $message->issueKey);
        $this->assertEquals($userBody, $message->userBody);
        $this->assertEquals('Implemented', $message->verb);
        $this->assertEquals('I', $message->shortVerb);
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
