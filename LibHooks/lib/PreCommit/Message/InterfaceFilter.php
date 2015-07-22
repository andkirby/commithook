<?php
namespace PreCommit\Message;

use PreCommit\Message;

/**
 * Class InterfaceFilter
 *
 * @package PreCommit\Message
 */
interface InterfaceFilter
{
    /**
     * Filer message
     *
     * @param \PreCommit\Message $message
     * @return string Message body
     */
    public function filter(Message $message);
}
