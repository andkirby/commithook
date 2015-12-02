<?php
namespace PreCommit\Validator\Message;

use PreCommit\Message;

/**
 * Class FilterInterface
 *
 * @package PreCommit\Filter
 */
interface ValidatorInterface
{
    /**
     * Validate message
     *
     * @param Message $message
     * @return bool
     */
    public function validate(Message $message);
}
