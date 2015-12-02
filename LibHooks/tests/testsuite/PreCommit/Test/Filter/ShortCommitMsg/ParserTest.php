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
     * Data provider
     *
     * @return array
     */
    public function dataProvider()
    {
        return array(
            /**
             * Test getting default verb by issue type
             */
            array(
                'TEST-123', //issue key
                'Test summary!!!', //issue summary
                'task', //issue type
                'Task', //original issue type
                "My test 1!\nTest 2.", //user message
                "123 My test 1!\nTest 2.", //full commit message
                'Implemented', //verb
                '', //short verb
            ),
            /**
             * Test getting verb by short verb
             */
            array(
                'TEST-123', //issue key
                'Test summary!!!', //issue summary
                'task', //issue type
                'Task', //original issue type
                "My test 1!\nTest 2.", //user message
                "F 123 My test 1!\nTest 2.", //full commit message
                'Fixed', //verb
                'F', //short verb
            ),
            /**
             * Test working with full issue key
             */
            array(
                'TEST-123', //issue key
                'Test summary!!!', //issue summary
                'task', //issue type
                'Task', //original issue type
                "My test 1!\nTest 2.", //user message
                "F TEST-123 My test 1!\nTest 2.", //full commit message
                'Fixed', //verb
                'F', //short verb
            ),
        );
    }

    /**
     * Test simple short message
     *
     * @param string $issueKey
     * @param string $summary
     * @param string $type
     * @param string $originalType
     * @param string $userBody
     * @param string $commitMessage
     * @param string $verb
     * @param string $shortVerb
     * @dataProvider dataProvider
     */
    public function testFilterShortMessageWithoutVerb(
        $issueKey, $summary, $type, $originalType, $userBody, $commitMessage, $verb, $shortVerb
    )
    {
        $message       = new Message();
        $message->body = $commitMessage;

        $issue = $this->_getIssueMock($summary, $issueKey, $type, $originalType);

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
        $this->assertEquals($verb, $message->verb);
        $this->assertEquals($shortVerb, $message->shortVerb);
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
        /** @var Issue\AbstractAdapter|\PHPUnit_Framework_MockObject_MockObject $filter */
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
