<?php
/**
 * Class test for Validator_CommitMsg
 */
class PreCommit_Validator_CommitMsgTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test CODE_BAD_COMMIT_MESSAGE. Negative test
     */
    public function testMessageFailure()
    {
        $processor = $this->_prepareModelAndProcess('My message.');
        $errors = $processor->getErrors();
        $errors = $errors['Commit Message'][\PreCommit\Validator\CommitMsg::CODE_BAD_COMMIT_MESSAGE];
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
            array('CR Changes ASDF-1234: Some text'),
            array('Refactored WE-1234: Some text'),
        );
    }

    /**
     * Test CODE_BAD_COMMIT_MESSAGE. Positive test
     *
     * @dataProvider dataMessageSuccess
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
     * @return PHPUnit_Framework_MockObject_MockObject|\PreCommit\Processor\CommitMsg
     */
    protected function _prepareModelAndProcess($message)
    {
        PreCommit\Config::getInstance(array('file' => PROJECT_ROOT . '/commithook.xml'));
        $vcsAdapter = $this->getMock('PreCommit\Vcs\Git');
        $vcsAdapter->expects($this->once())
            ->method('getCommitMessage')
            ->will($this->returnValue($message));

        /** @var PreCommit\Processor\CommitMsg|PHPUnit_Framework_MockObject_MockObject $processor */
        $processor = $this->getMock('PreCommit\Processor\CommitMsg', array('_getVcsAdapter'), array($vcsAdapter));

        $processor->setCodePath(PROJECT_ROOT);
        $processor->process();
        return $processor;
    }
}
