<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
namespace PreCommit\Filter\CommitMsg;

use PreCommit\Config;
use PreCommit\Message;

/**
 * Class Explode filter
 *
 * It will explode commit user message into a list
 *
 * @package PreCommit\CommitMsg
 */
class Explode implements Message\FilterInterface
{
    /**
     * Filter short commit message
     *
     * @param \PreCommit\Message $message
     * @return string
     */
    public function filter(Message $message)
    {
        if (!$this->enabled()) {
            return $message;
        }

        $message->body = trim($message->body);

        if ($message->userBody) {
            $updated = $this->explodeUserBody($message);
            if ($updated) {
                $message->body     = str_replace($message->userBody, $updated, $message->body);
                $message->userBody = $updated;
            }
        }

        return $message;
    }

    /**
     * Check status
     *
     * @return bool
     */
    protected function enabled()
    {
        return (bool) $this->getConfig()->getNode('filters/CommitMsg-Explode/explode_to_list');
    }

    /**
     * Get delimiter to explode
     *
     * @return null|string
     */
    protected function getDelimiter()
    {
        return $this->getConfig()->getNode('filters/CommitMsg-Explode/explode_string');
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
     * Explode user message
     *
     * @param Message $message
     * @return array
     */
    protected function explodeUserBody(Message $message)
    {
        $lines         = explode($this->getDelimiter(), $message->userBody);
        $saveDelimiter = trim($this->getDelimiter()) === '.';

        if (!(count($lines) > 1)) {
            return null;
        }

        foreach ($lines as &$line) {
            $line = ' - '.trim($line, ' -');
        }

        return implode(
            ($saveDelimiter ? trim($this->getDelimiter()) : '')."\n",
            $lines
        );
    }
}
