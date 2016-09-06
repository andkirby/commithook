<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
namespace PreCommit\Filter;

use PreCommit\Config;
use PreCommit\Filter\ShortCommitMsg\Parser;
use PreCommit\Interpreter\InterpreterInterface;
use PreCommit\Message;

/**
 * Class ShortCommitMsg filter
 *
 * @package PreCommit\Filter
 */
class ShortCommitMsg implements Message\FilterInterface
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
        $result = $this->getParser()->interpret($message);

        if (!$result || !$message->issueKey || !$message->summary || !$message->verb) {
            //the message wasn't parsed correctly
            return $message;
        }
        $this->buildMessage($message);

        return $message;
    }

    /**
     * Build message
     *
     * @param Message $message
     * @return $this
     * @throws \PreCommit\Exception
     */
    protected function buildMessage(Message $message)
    {
        $this->getFormatter()->filter($message);

        return $this;
    }

    /**
     * Get regular expressions to match
     *
     * @return array|null
     */
    protected function getFormatterConfig()
    {
        return $this->getConfig()->getNodeArray('filters/ShortCommitMsg/issue/formatter');
    }

    /**
     * Get parser
     *
     * @return Parser\IssueParserInterface|InterpreterInterface
     * @throws Exception
     */
    protected function getParser()
    {
        $parser = Parser::factory(null);
        if (!$parser) {
            throw new Exception('Could not local parser.');
        }

        return $parser;
    }

    /**
     * Get message builder
     *
     * @return FilterInterface
     * @throws Exception
     */
    protected function getFormatter()
    {
        $config = $this->getFormatterConfig();
        if (empty($config['class'])) {
            throw new Exception('Interpreter class is not set.');
        }
        $class  = $config['class'];
        /** @var FilterInterface $interpreter */
        if (empty($config['options'])) {
            return new $class();
        } else {
            return new $class(
                $config['options']
            );
        }
    }

    /**
     * Get config model
     *
     * @return Config
     */
    protected function getConfig()
    {
        return Config::getInstance();
    }

    /**
     * Get tracker type
     *
     * @return string
     */
    protected function getTrackerType()
    {
        return (string) $this->getConfig()->getNode('tracker/type');
    }
}
