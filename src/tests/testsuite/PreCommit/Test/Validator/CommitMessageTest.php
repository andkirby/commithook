<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
namespace PreCommit\Test\Validator;

use PreCommit\Message;
use PreCommit\Processor\ErrorCollector;
use PreCommit\Validator\CommitMsg;

/**
 * Class test for CommitMsg
 */
class CommitMsgTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test CODE_BAD_COMMIT_MESSAGE. Negative test
     */
    public function testMessageFailure()
    {
        $message        = new Message();
        $message->body  = 'My message.';
        $message->head  = 'My message.';
        $errorCollector = new ErrorCollector();
        $validator      = new CommitMsg(['errorCollector' => $errorCollector]);

        $this->assertFalse($validator->validate($message, null));

        $errors = $errorCollector->getErrors();
        $this->assertCount(1, $errors);

        $errors = array_shift($errors);
        $this->assertArrayHasKey(CommitMsg::CODE_BAD_COMMIT_MESSAGE, $errors);

        $errors = array_shift($errors);
        $this->assertCount(1, $errors);
    }

    /**
     * Data provider testMessageSuccess()
     *
     * @return array
     */
    public function dataMessageSuccess()
    {
        return [
            ['Implemented ASDF-1234: Some text'],
            ['Fixed ASDF-1234: Some text'],
            ['CR Change ASDFQWER-1: Some text'],
            ['CR Changes AS0099DF-1234: Some text'],
            ['Refactored WE2-1234: Some text'],
        ];
    }

    /**
     * Test CODE_BAD_COMMIT_MESSAGE. Positive test
     *
     * @dataProvider dataMessageSuccess
     * @param string $message
     */
    public function testMessageSuccess($message)
    {
        $msg            = new Message();
        $msg->body      = $message;
        $msg->head      = $message;
        $errorCollector = new ErrorCollector();
        $validator      = new CommitMsg(['errorCollector' => $errorCollector]);

        $this->assertTrue($validator->validate($msg, null));
    }
}
