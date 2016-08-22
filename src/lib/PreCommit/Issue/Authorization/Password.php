<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
namespace PreCommit\Issue\Authorization;

use Rikby\Crypter\Crypter as Crypter;
use Rikby\Crypter\CrypterInterface as CrypterInterface;
use PreCommit\Config;
use PreCommit\Exception as UserException;
use PreCommit\Exception;

/**
 * Class Password
 *
 * @package PreCommit\Jira
 */
class Password extends Crypter implements CrypterInterface
{
    /**
     * Password max length
     *
     * @var int
     * @todo Move to config file
     */
    protected $maxLength = 30;

    /**
     * Get password
     *
     * @param string $trackerType
     * @return null|string
     * @throws \PreCommit\Exception
     */
    public function getPassword($trackerType)
    {
        try {
            $password = Config::getInstance()->getNode(
                'tracker/'.$trackerType.'/password'
            );
            if (!$password) {
                return null;
            }
            if (strlen($password) < $this->maxLength) {
                //password doesn't look like encrypted
                return $password;
            }

            return $this->decrypt($password) ?: null;
        } catch (Exception $e) {
            throw new UserException('Cannot get password of tracker.', 0, $e);
        }
    }

    /**
     * Replace strange \000 character
     *
     * {@inheritdoc}
     */
    public function decrypt($string, $key = null)
    {
        $password = parent::decrypt($string, $key);

        /**
         * Unknown bug in PhpStorm
         * Only PHPStorm adds \0 to the tail of password
         * Perhaps PHP versions conflict
         */
        return rtrim($password, "\000");
    }
}
