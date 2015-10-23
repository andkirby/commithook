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
}
