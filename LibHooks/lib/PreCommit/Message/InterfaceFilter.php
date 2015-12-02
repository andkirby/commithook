<?php
namespace PreCommit\Message;

use PreCommit\Message;

/**
 * Class FilterInterface
 *
 * @package PreCommit\Message
 */
interface InterfaceFilter
{
    /**
     * Filer message
     *
     * @param \PreCommit\Message $message
     * @return Message
     */
    public function filter(Message $message);
}
