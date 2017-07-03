<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
namespace testsuite\PreCommit\Test\Filter\ShortCommitMsg;

use PreCommit\Filter\Explode;
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
                'TEST', // project key
                'jira', // tracker type
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
                'TEST', //project key
                'jira', //tracker type
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
                'TEST', // project key
                'jira', // tracker type
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
                'TEST', // project key
                'jira', // tracker type
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
                'TEST', // project key
                'jira', // tracker type
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
     * @param string $projectKey
     * @param string $trackerType
     * @param string $resultValue
     * @dataProvider dataProvider
     */
    public function testFilterShortMessageWithoutVerb(
        $issueKey,
        $summary,
        $type,
        $originalType,
        $commitMessage,
        $projectKey,
        $trackerType,
        $resultValue
    ) {
        // Real message.
        $message = new Message();

        // Explode filter
        $explodeFilter = new Explode();

        // Prepare a commit message
        $message->body = $commitMessage;

        // Filter commit message.
        $message = $explodeFilter->filter($message);
        $issue = $this->getIssueMock($summary, $issueKey, $type, $originalType);


        // Create a config object mock
        $configMock = $this->getMockBuilder('PreCommit\Config')
            ->getMock();

        $map = [
            [
                "hooks/commit-msg/message/verb/list/", true, [
                "I" => "Implemented",
                "R" => "Refactored",
                "F" => "Fixed",
                "C" => "CR Changes"
            ]
            ],
            [
                "formatters/ShortCommitMsg/formatting/$trackerType", true, [
                "regular" => "~^__format__~",
                "format"  => "__verb__ __issue_key__: __summary__",
            ],
            ]
        ];

        $mapNode = [
            ["tracker/type", true, $trackerType],
            ["tracker/jira/project", true, $projectKey],
            ["tracker/jira/active_task", true, $issueKey],
            ["filters/ShortCommitMsg/issue/default_type_verb/task", true, "I"],
            ["filters/ShortCommitMsg/issue/default_type_verb/bug", true, "F"],
        ];

        // Configure the getNodeArray stub.
        $configMock->method('getNodeArray')
            ->will($this->returnValueMap($map));

        // Configure the getNode stub.
        $configMock->method('getNode')
            ->will($this->returnValueMap($mapNode));

        $reflector = new \ReflectionClass("\\PreCommit\\Filter\\ShortCommitMsg\\Parser\\Jira");

        // A new parser object. Set a custom config mock to a config property.

        // Create a new instance without constructor.
        $parser = $reflector->newInstanceWithoutConstructor();
        $configProperty = $reflector->getProperty("config");
        $configProperty->setAccessible(true);
        $configProperty->setValue($parser, $configMock);

        // Specify an "issue" property. Add a custom mock object as an issue property.
        $issueProperty = $reflector->getProperty("issue");
        $issueProperty->setAccessible(true);
        $issueProperty->setValue($parser, $issue);

        // Interpret original commit message.
        $result = $parser->interpret($message);

        $reflector = new \ReflectionClass("\\PreCommit\\Filter\\ShortCommitMsg\\Formatter");

        $formatter = $reflector->newInstanceWithoutConstructor();
        $configProperty = $reflector->getProperty("config");

        // Get a formatter type property, set a tracker type for the Formatter.
        $typeProperty = $reflector->getProperty("type");
        $typeProperty->setAccessible(true);
        $typeProperty->setValue($formatter, $trackerType);

        // Get a formatter config property, set a config model for the Formatter.
        $configProperty->setAccessible(true);
        $configProperty->setValue($formatter, $configMock);

        $resultMessage = $formatter->filter($result);

        // Verify commit messages
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
        /** @var Issue\AbstractAdapter|\PHPUnit_Framework_MockObject_MockObject $filter */
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
