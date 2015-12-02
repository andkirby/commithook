<?php
namespace PreCommit\Interpreter;

use PreCommit\Config;
use PreCommit\Exception;
use PreCommit\Message;

/**
 * Class FullCommitMsg
 *
 * @package PreCommit\Interpreter
 */
class FullCommitMsg implements InterpreterInterface
{
    /**
     * Message interpreting type
     *
     * @var string
     */
    protected $type;

    /**
     * Input commit message
     *
     * @var string
     */
    protected $message;

    /**
     * Set type
     *
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        if (isset($options['type'])) {
            $this->type = $options['type'];
        } else {
            $this->type = $this->getConfig()->getNode('hooks/commit-msg/message/type');
        }
    }

    /**
     * Interpret data
     *
     * @param array $data
     * @return array
     * @throws \PreCommit\Exception
     */
    public function interpret($data)
    {
        if (empty($data['message'])) {
            throw new Exception('Message data object is not set.');
        }
        $message = $data['message'];
        if (!($message instanceof Message)) {
            throw new Exception('Wrong message data object instance set.');
        }

        preg_match($this->getRegular(), $message->body, $matches);

        $result = array();
        array_shift($matches); //ignore match all
        foreach ($this->getKeys() as $name => $regular) {
            if ($matches) {
                $result[$name] = array_shift($matches);
            }
        }

        return $result;
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
     * Get format
     *
     * @return string
     * @throws \PreCommit\Exception
     */
    protected function getFormat()
    {
        $format = $this->getConfig()->getNode('interpreters/FullCommitMsg/formatting/'.$this->type.'/format');
        if (!$format) {
            throw new Exception('Format regular expression is not set.');
        }

        return $format;
    }

    /**
     * Get base regular template
     *
     * @return string
     * @throws \PreCommit\Exception
     */
    protected function getRegularFormat()
    {
        $regular = $this->getConfig()->getNode('interpreters/FullCommitMsg/formatting/'.$this->type.'/regular');
        if (!$regular) {
            throw new Exception('Base regular expression is not set.');
        }

        return $regular;
    }

    /**
     * Get keys
     *
     * @return array
     * @throws \PreCommit\Exception
     */
    protected function getKeys()
    {
        $keys = $this->getConfig()->getNodeArray('interpreters/FullCommitMsg/formatting/'.$this->type.'/key');
        if (!$keys) {
            throw new Exception('Key regular expressions is not set.');
        }

        return $keys;
    }

    /**
     * Get complete regular expression based upon format and keys
     *
     * @return array
     */
    protected function getRegular()
    {
        $format = $this->getFormat();
        foreach ($this->getKeys() as $name => $regular) {
            $format = str_replace("__{$name}__", "($regular)", $format);
        }

        return str_replace("__format__", "{$format}", $this->getRegularFormat());
    }
}
