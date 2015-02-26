<?php
namespace PreCommit\Jira;

use PreCommit\Exception as UserException;

require_once '_password.php';

/**
 * Class Password
 *
 * @package PreCommit\Jira
 */
class Password extends \ILBYNINKHULN\Password
{
    /**
     * Get password and catch an exception
     *
     * @return string
     * @throws UserException
     */
    public function getPassword()
    {
        try {
            return parent::getPassword();
        } catch (\ILBYNINKHULN\PasswordException $e) {
            throw new UserException($e->getMessage());
        }
    }
}
