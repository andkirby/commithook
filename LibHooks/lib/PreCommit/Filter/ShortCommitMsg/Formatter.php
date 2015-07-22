<?php
namespace PreCommit\Filter\ShortCommitMsg;

use PreCommit\Config;
use PreCommit\Exception;
use PreCommit\Filter\InterfaceFilter;
use PreCommit\Issue;
use PreCommit\Jira\Api;
use PreCommit\Message;

/**
 * Formatter prepare commit message according to format
 *
 * @package PreCommit\Validator
 */
class Formatter implements Message\InterfaceFilter
{
    /**
     * Message interpreting type
     *
     * @var string
     */
    protected $_type;

    /**
     * Set type
     *
     * @param array $options
     * @throws \PreCommit\Exception
     */
    public function __construct(array $options = array())
    {
        if (isset($options['type'])) {
            $this->_type = $options['type'];
        } else {
            $this->_type = $this->_getConfig()->getNode('hooks/commit-msg/message/type');
        }
        if (!$this->_type) {
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
        return $this->_buildMessage(
            $this->_getFormatConfig(), $message
        );
    }

    /**
     * Build message
     *
     * @param array $config  Config for formatting
     * @param Message $message
     * @return string
     * @throws \PreCommit\Exception
     */
    protected function _buildMessage($config, $message)
    {
        $output = $config['format'];
        //make default keys list
        $keys   = array(
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
                    $keys[$name] = $this->_getConfig()->getNode($keyConfig['xpath']);
                } else {
                    throw new Exception("Please set 'xpath' node with a path to a local value.");
                }
            }
        }
        $output = $this->_putKeys($keys, $output);
        return $output;
    }

    /**
     * Get format
     *
     * @return string
     * @throws \PreCommit\Exception
     */
    protected function _getFormatConfig()
    {
        $format = $this->_getConfig()->getNodeArray('formatters/ShortCommitMsg/formatting/' . $this->_type);
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
     */
    protected function _putKeys(array $keys, $output)
    {
        foreach ($keys as $name => $value) {
            $output = str_replace("__{$name}__", $value, $output);
        }
        return $output;
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
