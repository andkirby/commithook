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
        $processor = $this->_prepareModelAndProcess('My message.');
        $errors = $processor->getErrors();
        $errors = $errors['Commit Message'][CommitMsg::CODE_BAD_COMMIT_MESSAGE];
        $expected = array (
            'value'   => 'My message.',
            'message' => 'Your commit message "My message." has improper form.',
        );
        $this->assertEquals($expected, $errors[0]);
    }

    /**
     * Data provider testMessageSuccess()
     *
     * @return array
     */
    public function dataMessageSuccess()
    {
        return array(
            array('Implemented ASDF-1234: Some text'),
            array('Fixed ASDF-1234: Some text'),
            array('CR Change ASDFQWER-1: Some text'),
            array('CR Changes AS0099DF-1234: Some text'),
            array('Refactored WE2-1234: Some text'),
        );
    }

    /**
     * Test CODE_BAD_COMMIT_MESSAGE. Positive test
     *
     * @dataProvider dataMessageSuccess
     * @param string $message
     */
    public function testMessageSuccess($message)
    {
        $processor = $this->_prepareModelAndProcess($message);
        $this->assertEquals(array(), $processor->getErrors());
    }

    /**
     * Prepare model and method mocks
     *
     * @param string $message
     * @return \PreCommit\Processor\CommitMsg|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function _prepareModelAndProcess($message)
    {
        Config::getInstance(array('file' => PROJECT_ROOT . '/commithook.xml'));
        Config::setSrcRootDir(PROJECT_ROOT);
        $vcsAdapter = $this->getMock('PreCommit\Vcs\Git');
        $vcsAdapter->expects($this->once())
            ->method('getCommitMessage')
            ->will($this->returnValue($message));

        /** @var CommitMsg|\PHPUnit_Framework_MockObject_MockObject $processor */
        $processor = $this->getMock('PreCommit\Processor\CommitMsg', array('_getVcsAdapter'), array($vcsAdapter));

        $processor->setCodePath(PROJECT_ROOT);
        $processor->process();
        return $processor;
    }
}
