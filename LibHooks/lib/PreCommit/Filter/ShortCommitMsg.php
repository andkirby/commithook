<?php
namespace PreCommit\Filter;

use PreCommit\Config;
use PreCommit\Exception;

/**
 * Class ShortCommitMsg filter
 *
 * @package PreCommit\Filter
 */
class ShortCommitMsg implements InterfaceFilter
{
    /**
     * Filter short commit message
     *
     * @param string $inputMessage
     * @param string|null   $file
     * @return string
     */
    public function filter($inputMessage, $file = null)
    {
        $inputMessage = trim($inputMessage);
        //JIRA is the one issue tracker so far
        //TODO implement factory loading
        $jira = new ShortCommitMsg\Jira();
        return $this->_buildMessage(
            $jira->filter($inputMessage, $file)
        );
    }

    /**
     * Build message
     *
     * @param array $options
     * @return string
     * @throws \PreCommit\Exception
     */
    protected function _buildMessage($options)
    {
        return $this->_getFormatter()
            ->filter($options);
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
