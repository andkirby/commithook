<?php
namespace PreCommit\Filter;

use PreCommit\Config;
use PreCommit\Exception;
use PreCommit\Issue;
use PreCommit\Message;

/**
 * Class ShortCommitMsg filter
 *
 * @package PreCommit\Filter
 */
class ShortCommitMsg implements Message\InterfaceFilter
{
    /**
     * Filter short commit message
     *
     * @param \PreCommit\Message $message
     * @return string
     */
    public function filter(Message $message)
    {
        $message->body = trim($message->body);
        //JIRA is the one issue tracker so far
        //TODO implement factory parser loading
        $result = $this->_getParser()->interpret($message);

        if (!$result || empty($result['issueKey'])) {
            //the message wasn't parsed correctly
            return $message;
        }
        $this->_buildMessage($message);
        return $message;
    }

    /**
     * Build message
     *
     * @param Message $message
     * @return $this
     * @throws \PreCommit\Exception
     */
    protected function _buildMessage(Message $message)
    {
        $this->_getFormatter()->filter($message);
        return $this;
    }

    /**
     * Get regular expressions to match
     *
     * @return array|null
     */
    protected function _getFormatterConfig()
    {
        return $this->_getConfig()->getNodeArray('filters/ShortCommitMsg/issue/formatter');
    }

    /**
     * Get parser
     *
     * @return \PreCommit\Filter\ShortCommitMsg\Parser
     */
    protected function _getParser()
    {
        return new ShortCommitMsg\Parser();
    }

    /**
     * Get message builder
     *
     * @return InterfaceFilter
     * @throws Exception
     */
    protected function _getFormatter()
    {
        $config = $this->_getFormatterConfig();
        if (empty($config['class'])) {
            throw new Exception('Interpreter class is not set.');
        }
        /** @var InterfaceFilter $interpreter */
        if (empty($config['options'])) {
            return new $config['class'];
        } else {
            return new $config['class'](
                $config['options']
            );
        }
    }

    /**
     * Get config model
     *
     * @return Config
     */
    protected function _getConfig()
    {
        return Config::getInstance();
    }
}
