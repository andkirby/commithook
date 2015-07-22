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
                "Implemented TEST-123: Test summary!!!"
            )
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
     * @param string $expected
     * @dataProvider dataProvider
     */
    public function testMessageBodyFormatting(
        $issueKey, $summary, $type, $originalType, $userBody, $commitMessage, $verb, $shortVerb, $expected
    ) {
        $message            = new Message();
        $message->body      = $commitMessage;
        $message->issueKey  = $issueKey;
        $message->summary   = $summary;
        $message->userBody  = $userBody;
        $message->verb      = $verb;
        $message->shortVerb = $shortVerb;
        $message->issue     = $this->_getIssueMock($summary, $issueKey, $type, $originalType);

        /** @var ShortCommitMsg\Formatter|\PHPUnit_Framework_MockObject_MockObject $formatter */
        $formatter = new ShortCommitMsg\Formatter();
        $this->assertEquals($expected, $formatter->filter($message));
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
