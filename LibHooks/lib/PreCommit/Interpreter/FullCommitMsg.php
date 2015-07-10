<?php
namespace PreCommit\Interpreter;

use PreCommit\Config;
use PreCommit\Exception;

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
    protected $_type;

    /**
     * Input commit message
     *
     * @var string
     */
    protected $_message;

    /**
     * Set type
     *
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        $this->_type = isset($options['type']) ? $options['type'] : 'default';
    }

    /**
     * Interpret data
     *
     * @param array $data
     * @return array
     * @throws \PreCommit\Exception
     */
    public function interpret(array $data)
    {
        if (empty($data['message'])) {
            throw new Exception('commit_message key data is not set.');
        }

        preg_match($this->_getRegular(), $data['message'], $matches);

        $result = array();
        array_shift($matches); //ignore match all
        foreach ($this->_getKeys() as $name => $regular) {
            $result[$name] = array_shift($matches);
        }
        return $result;
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

    /**
     * Get format
     *
     * @return string
     * @throws \PreCommit\Exception
     */
    protected function _getFormat()
    {
        $format = $this->_getConfig()->getNode('interpreters/FullCommitMsg/formatting' . $this->_type . '/format');
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
    protected function _getRegularFormat()
    {
        $regular = $this->_getConfig()->getNode('interpreters/FullCommitMsg/formatting' . $this->_type . '/regular');
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
    protected function _getKeys()
    {
        $keys = $this->_getConfig()->getNodeArray('interpreters/FullCommitMsg/formatting' . $this->_type . '/key');
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
    protected function _getRegular()
    {
        $format = $this->_getFormat();
        foreach ($this->_getKeys() as $name => $regular) {
            $format = str_replace("__{$name}__", "($regular)", $format);
        }
        return str_replace("__format__", "{$format}", $this->_getRegularFormat());
    }
}
