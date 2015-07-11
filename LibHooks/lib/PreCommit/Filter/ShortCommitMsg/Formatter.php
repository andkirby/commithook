<?php
namespace PreCommit\Filter\ShortCommitMsg;

use PreCommit\Config;
use PreCommit\Exception;
use PreCommit\Filter\InterfaceFilter;
use PreCommit\Issue;
use PreCommit\Jira\Api;

/**
 * Class validator for check commit message format
 *
 * @package PreCommit\Validator
 */
class Formatter implements InterfaceFilter
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
            $this->_type = $this->_getConfig()->getNode('hooks/commit-msg/message_type');
        }
        if (!$this->_type) {
            throw new Exception('Type is not set.');
        }
    }

    /**
     * Build commit message
     *
     * @param array       $options
     * @param null|string $file Ignored param
     * @return string
     */
    public function filter($options, $file = null)
    {
        return $this->_buildMessage(
            $this->_getFormatConfig(), $options
        );
    }

    /**
     * Build message
     *
     * @param array $config  Config for formatting
     * @param array $options key-value parsed data
     * @return string
     */
    protected function _buildMessage($config, $options)
    {
        $output = $config['format'];
        $keys   = $options['keys'];
        $output = $this->_putKeys($keys, $output);

        /**
         * Put extra static keys
         */
        if (!empty($config['key']) && is_array($config['key'])) {
            foreach ($config['key'] as $name => $keyConfig) {
                if (isset($keyConfig['xpath'])) {
                    $keyValue = $this->_getConfig()->getNode($keyConfig['xpath']);
                    $output   = $this->_putKeys(
                        array($name => $keyValue), $output
                    );
                }
            }
        }
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
