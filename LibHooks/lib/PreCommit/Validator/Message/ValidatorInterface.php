<?php
namespace PreCommit\Validator\Message;

use PreCommit\Message;

/**
 * Class InterfaceFilter
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
