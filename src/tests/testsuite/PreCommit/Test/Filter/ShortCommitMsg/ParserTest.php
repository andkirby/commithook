<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
namespace testsuite\PreCommit\Test\Filter\ShortCommitMsg;

use PreCommit\Filter\Explode;
use PreCommit\Filter\ShortCommitMsg\Formatter;
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
        return [
            /**
             * Test getting default verb by issue type
             */
            [
                'TEST-123', //issue key
                'Test summary!!!', //issue summary
                'task', //issue type
                'Task', //original issue type
                "R 123 My new test header \nTest 2.", //full commit message
                "Refactored TEST-123: Test summary!!!\n - My new test header\nTest 2."
            ],

            /**
             * Test getting default verb by issue type
             */
            [
                'TEST-1', //issue key
                'Test summary!!!', //issue summary
                'task', //issue type
                'Task', //original issue type
                "I 1 My new test header \nTest 2.", //full commit message
                "Implemented TEST-1: Test summary!!!\n - My new test header\nTest 2."
            ],

            /**
             * Test getting default verb by issue type
             */
            [
                'TEST-1', // issue key
                'Test summary!!!', // issue summary
                'task', // issue type
                'Task', // original issue type
                "1 \nTest 2.", // full commit message
                "Implemented TEST-1: Test summary!!!\n - Test 2."
            ],
            /**
             * Test getting default verb by issue type
             */
            [
                'TEST-12', // issue key
                'Test summary!!!', // issue summary
                'task', // issue type
                'Task', // original issue type
                "12 Test 1 \nTest 2.", // full commit message
                "Implemented TEST-12: Test summary!!!\n - Test 1\nTest 2."
            ],
            /**
             * Test getting default verb by issue type
             */
            [
                'TEST-22', //issue key
                'Test summary!!!', //issue summary
                'task', //issue type
                'Task', //original issue type
                "C 22 My new test header \nTest 2.", //full commit message
                "CR Changes TEST-22: Test summary!!!\n - My new test header\nTest 2."
            ]
        ];
    }

    /**
     * Test simple short message
     *
     * @param string $issueKey
     * @param string $summary
     * @param string $type
     * @param string $originalType
     * @param string $commitMessage
     * @param string $resultValue
     * @dataProvider dataProvider
     */
    public function testFilterShortMessageWithoutVerb(
        $issueKey,
        $summary,
        $type,
        $originalType,
        $commitMessage,
        $resultValue
    ) {

        $message = new Message();
        $explodeFilter = new Explode();
        $formatter = new Formatter();

        // Prepare a commit message
        $message->body = $commitMessage;

        // Filter commit message through the explode filter.
        $message = $explodeFilter->filter($message);
        $issue = $this->getIssueMock($summary, $issueKey, $type, $originalType);

        // Getting jira parser reflector.
        $reflector = new \ReflectionClass("\\PreCommit\\Filter\\ShortCommitMsg\\Parser\\Jira");

        // Create a new instance from reflector.
        $parser = $reflector->newInstance();

        // Specify an "issue" property. Add a custom mock object as an issue property.
        $issueProperty = $reflector->getProperty("issue");
        $issueProperty->setAccessible(true);
        $issueProperty->setValue($parser, $issue);

        // Interpret original commit message.
        $result = $parser->interpret($message);

        // Filter result message through a formatter object.
        $resultMessage = $formatter->filter($result);
        $this->assertEquals($resultMessage->__toString(), $resultValue);
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
    protected function getIssueMock($summary, $key, $type, $originalType)
    {
        /** @var Issue\AbstractAdapter|\PHPUnit_Framework_MockObject_MockObject $issue */
        $issue = $this->getMockBuilder('PreCommit\Issue\AdapterAbstract')
            ->setMethods(['getSummary', 'getKey', 'getType', 'getOriginalType'])
            ->getMock();

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
