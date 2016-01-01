<?php
namespace PreCommit\Processor;

use PreCommit\Message;
use PreCommit\Validator\CommitMsg as ValidatorCommitMsg;

/**
 * Class abstract process adapter
 *
 * @package PreCommit\Processor
 */
class CommitMsg extends AbstractAdapter
{
    /**
     * Path to root of code
     *
     * @var string
     */
    protected $codePath;

    /**
     * Set adapter data from config
     *
     * @param array|string $vcsType
     * @throws \PreCommit\Exception
     */
    public function __construct($vcsType)
    {
        parent::__construct($vcsType);
        $this->setCodePath($this->vcsAdapter->getCodePath());
    }

    /**
     * Set code path
     *
     * @param string $codePath
     * @return $this
     */
    public function setCodePath($codePath)
    {
        $this->codePath = $codePath;

        return $this;
    }

    /**
     * Process commit message
     *
     * @return bool
     * @throws \Exception
     */
    public function process()
    {
        $message = new Message();

        $message->body = $this->getCommitMessage();

        try {
            $message = $this->loadFilter('Explode')
                ->filter($message);

            /** @var ValidatorCommitMsg $commitMsg */
            $commitMsg = $this->loadValidator('CommitMsg');

            if (!$commitMsg->validate($message, null, true)) {
                $message = $this->loadFilter('ShortCommitMsg')
                    ->filter($message);
            }

            $this->loadValidator('IssueType')
                ->validate($message, null);

            $this->loadValidator('CommitMsg')
                ->validate($message, null);

            $this->loadValidator('IssueStatus')
                ->validate($message, null);
        } catch (\Exception $e) {
            //TODO refactor ignore issue approach
            if ($message->issue) {
                //ignore issue caching on failed validation
                $message->issue->ignoreIssue();
            }
            throw $e;
        }

        if ($this->errorCollector->hasErrors() && $message->issue) {
            //ignore issue caching on failed validation
            $message->issue->ignoreIssue();
        } else {
            $this->setCommitMessage($message);
        }

        return !$this->errorCollector->hasErrors();
    }

    /**
     * Get commit message
     *
     * @return string
     */
    protected function getCommitMessage()
    {
        return $this->vcsAdapter->getCommitMessage();
    }

    /**
     * Set commit message
     *
     * @param \PreCommit\Message $message
     * @return string
     */
    protected function setCommitMessage(Message $message)
    {
        return $this->vcsAdapter->setCommitMessage($message->body);
    }
}
