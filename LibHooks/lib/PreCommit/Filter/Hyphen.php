<?php
namespace PreCommit\Filter;

use PreCommit\Message;

/**
 * Class ShortCommitMsg filter
 *
 * @package PreCommit\Filter
 */
class Hyphen implements Message\InterfaceFilter
{
    /**
     * Filter short commit message
     *
     * @param \PreCommit\Message $message
     * @return string
     */
    public function filter(Message $message)
    {
        $message->userBody = $this->_addHyphen($message->userBody);
        return $message;
    }

    /**
     * Add "-" to comment row
     *
     * @param string $comment
     * @return string
     */
    protected function _addHyphen($comment)
    {
        if ($comment && 0 !== strpos(trim($comment), '-')) {
            $comment = ' - ' . $comment;
        }
        return $comment;
    }
}
