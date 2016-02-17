<?php
/**
 * @license https://raw.githubusercontent.com/andkirby/commithook/master/LICENSE.md
 */
namespace AndKirby\Crypter;

/**
 * Class Crypter
 *
 * @package AndKirby\Crypter
 */
interface CrypterInterface
{
    /**
     * Encrypt string
     *
     * @param string      $string
     * @param null|string $key    Encrypt key
     * @return string
     */
    public function encrypt($string, $key = null);

    /**
     * Decrypt string
     *
     * @param string      $string
     * @param null|string $key    Encrypt key
     * @return string
     */
    public function decrypt($string, $key = null);
}
