<?php
namespace PreCommit\Filter\ShortCommitMsg;

use PreCommit\Config;
use PreCommit\Exception;
use PreCommit\Issue;
use PreCommit\Jira\Api;
use PreCommit\Message;

/**
 * Formatter prepare commit message according to format
 *
 * @package PreCommit\Validator
 */
class Formatter implements Message\FilterInterface
{
    /**
     * Message interpreting type
     *
     * @var string
     */
    protected $type;

    /**
     * Set type
     *
     * @param array $options
     * @throws \PreCommit\Exception
     */
    public function __construct(array $options = array())
    {
        if (isset($options['type'])) {
            $this->type = $options['type'];
        } else {
            $this->type = $this->getConfig()->getNode('hooks/commit-msg/message/type');
        }
        if (!$this->type) {
            throw new Exception('Type is not set.');
        }
    }

    /**
     * Build commit message
     *
     * @param Message $message
     * @return string
     */
    public function filter(Message $message)
    {
        $this->buildHead(
            $this->getFormatConfig(),
            $message
        );
        $this->buildBody($message);

        return $message;
    }

    /**
     * Build message
     *
     * @param array   $config Config for formatting
     * @param Message $message
     * @return string
     * @throws \PreCommit\Exception
     */
    protected function buildHead($config, Message $message)
    {
        $output = $config['format'];
        //make default keys list
        $keys = array(
            'summary'   => $message->summary,
            'issue_key' => $message->issueKey,
            'verb'      => $message->verb,
        );

        /**
         * Put extra static keys
         */
        if (!empty($config['key']) && is_array($config['key'])) {
            foreach ($config['key'] as $name => $keyConfig) {
                if (isset($keyConfig['xpath'])) {
                    $keys[$name] = $this->getConfig()->getNode($keyConfig['xpath']);
                } else {
                    throw new Exception("Please set 'xpath' node with a path to a local value.");
                }
            }
        }
        $message->head = $this->putKeys($keys, $output);

        return $this;
    }

    /**
     * Get format
     *
     * @return string
     * @throws \PreCommit\Exception
     */
    protected function getFormatConfig()
    {
        $format = $this->getConfig()->getNodeArray('formatters/ShortCommitMsg/formatting/'.$this->type);
        if (empty($format['format'])) {
            throw new Exception('Format to build commit message is not set.');
        }

        return $format;
    }

    /**
     * Put keys to output
     *
     * @param array  $keys
     * @param string $output
     * @return string
     * @throws \PreCommit\Exception
     */
    protected function putKeys(array $keys, $output)
    {
        foreach ($keys as $name => $value) {
            if (!$value) {
                throw new Exception("Variable '$name' cannot be empty.");
            }
            $output = str_replace("__{$name}__", $value, $output);
        }

        return $output;
    }

    /**
     * Build message body
     *
     * @param \PreCommit\Message $message
     * @return $this
     */
    protected function buildBody(Message $message)
    {
        $message->userBody = $this->addHyphen($message->userBody);
        $message->body     = $message->head."\n".$message->userBody;

        return $this;
    }

    /**
     * Add "-" to comment row
     *
     * @param string $comment
     * @return string
     */
    protected function addHyphen($comment)
    {
        if ($comment && 0 !== strpos(trim($comment), '-')) {
            $comment = ' - '.$comment;
        }

        return $comment;
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
}
