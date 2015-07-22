<?php
namespace PreCommit\Processor;
use \PreCommit\Exception as Exception;
use PreCommit\Message;

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
    protected $_codePath;

    /**
     * Set adapter data from config
     *
     * @param array|string $vcsType
     * @throws \PreCommit\Exception
     */
    public function __construct($vcsType)
    {
        parent::__construct($vcsType);
        $this->setCodePath($this->_vcsAdapter->getCodePath());
    }

    /**
     * Set code path
     *
     * @param string $codePath
     * @return $this
     */
    public function setCodePath($codePath)
    {
        $this->_codePath = $codePath;
        return $this;
    }

    /**
     * Process commit message
     *
     * @return bool
     * @throws Exception
     */
    public function process()
    {
        $message = new Message();
        \Zend_Registry::set('message', $message);

        $message->body = $this->_getCommitMessage();

        $message = $this->_loadFilter('Explode')
            ->filter($message);

        $message = $this->_loadFilter('ShortCommitMsg')
            ->filter($message);

        $message = $this->_loadFilter('Hyphen')
            ->filter($message);

        $this->_loadValidator('CommitMsg')
            ->validate($message, null);

        $this->_loadValidator('IssueType')
            ->validate($message, null);

        if (!$this->_errorCollector->hasErrors()) {
            $this->_setCommitMessage($message);
        }
        return !$this->_errorCollector->hasErrors();
    }

    /**
     * Get commit message
     *
     * @return string
     */
    protected function _getCommitMessage()
    {
        return $this->_vcsAdapter->getCommitMessage();
    }

    /**
     * Set commit message
     *
     * @param string $message
     * @return string
     */
    protected function _setCommitMessage($message)
    {
        return $this->_vcsAdapter->setCommitMessage($message);
    }
}
