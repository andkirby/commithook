<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
namespace PreCommit\Test\Validator;

use PreCommit\Config;
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
        $processor = $this->prepareModelAndProcess('My message.');
        $errors    = $processor->getErrors();
        $errors    = $errors['Commit Message'][CommitMsg::CODE_BAD_COMMIT_MESSAGE];
        $expected  = [
            'value'   => 'My message.',
            'message' => 'Your commit message "My message." has improper form.',
        ];
        $this->assertEquals($expected, $errors[0]);
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
        $processor = $this->prepareModelAndProcess($message);
        $this->assertEquals([], $processor->getErrors());
    }

    /**
     * Prepare model and method mocks
     *
     * @param string $message
     * @return \PreCommit\Processor\CommitMsg|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function prepareModelAndProcess($message)
    {
        Config::initInstance(['file' => PROJECT_ROOT.'/commithook.xml']);
        Config::setSrcRootDir(PROJECT_ROOT);
        $vcsAdapter = $this->getMock('PreCommit\Vcs\Git');
        $vcsAdapter->expects($this->once())
            ->method('getCommitMessage')
            ->will($this->returnValue($message));

        /** @var CommitMsg|\PHPUnit_Framework_MockObject_MockObject $processor */
        $processor = $this->getMock('PreCommit\Processor\CommitMsg', ['_getVcsAdapter'], [$vcsAdapter]);

        $processor->setCodePath(PROJECT_ROOT);
        $processor->process();

        return $processor;
    }
}
