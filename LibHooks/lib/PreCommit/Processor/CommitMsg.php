<?php
namespace PreCommit\Processor;
use \PreCommit\Exception as Exception;

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
    //endregion

    /**
     * @return bool
     * @throws Exception
     */
    public function process()
    {
        $message = $this->_loadFilter('JiraCommitMsg')
            ->filter($this->_getCommitMessage());

        $this->_loadValidator('CommitMsg')
            ->validate($message, null);

        $result = array() == $this->_errorCollector->getErrors();
        if ($result) {
            $this->_setCommitMessage($message);
        }
        return $result;
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
