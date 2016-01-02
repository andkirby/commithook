<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
namespace PreCommit\Message;

use PreCommit\Message;

/**
 * Class FilterInterface
 *
 * @package PreCommit\Message
 */
interface FilterInterface
{
    /**
     * Filer message
     *
     * @param \PreCommit\Message $message
     * @return Message
     */
    public function filter(Message $message);
}
