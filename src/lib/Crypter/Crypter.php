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
class Crypter implements CrypterInterface
{
    /**
     * Encryption key salt
     */
    static private $salt = 'bcb04b7e103a0cd8b5476305';

    /**
     * System ID
     *
     * @var string
     */
    static private $uuid;

    /**
     * Encrypt password
     *
     * @param string      $text Text to encrypt
     * @param null|string $key  Encrypt key
     * @return string
     * @link http://php.net/manual/ru/function.mcrypt-encrypt.php#refsect1-function.mcrypt-encrypt-examples
     */
    public function encrypt($text, $key = null)
    {
        if (null === $key) {
            $key = $this->getEncryptKey();
        }

        $ivSize = $this->getMsCryptIvSize();
        $iv     = mcrypt_create_iv($ivSize, MCRYPT_DEV_RANDOM);

        $cipherText = mcrypt_encrypt(
            $this->getMcryptCipher(),
            $key,
            $text,
            $this->getMcryptMode(),
            $iv
        );

        $cipherText = $iv.$cipherText;

        return base64_encode($cipherText);
    }

    /**
     * Decrypt string
     *
     * @param string      $string String to decrypt
     * @param null|string $key    Encrypt key
     * @return string
     */
    public function decrypt($string, $key = null)
    {
        if (null === $key) {
            $key = $this->getEncryptKey();
        }

        $cipherText = base64_decode($string);
        $ivSize     = $this->getMsCryptIvSize();

        $ivDec = substr($cipherText, 0, $ivSize);

        $cipherText = substr($cipherText, $ivSize);

        return mcrypt_decrypt(
            $this->getMcryptCipher(),
            $key,
            $cipherText,
            $this->getMcryptMode(),
            $ivDec
        );
    }

    /**
     * Get encrypt key
     *
     * @return string
     */
    protected function getEncryptKey()
    {
        return pack('H*', md5($this->getUuid().self::$salt));
    }

    /**
     * Get mcrypt IV size
     *
     * @return int
     */
    protected function getMsCryptIvSize()
    {
        return mcrypt_get_iv_size($this->getMcryptCipher(), $this->getMcryptMode());
    }

    /**
     * Get MCRYPT algorithm
     *
     * @return string
     */
    protected function getMcryptMode()
    {
        return MCRYPT_MODE_CBC;
    }

    /**
     * Get MCRYPT Cipher
     *
     * @return string
     */
    protected function getMcryptCipher()
    {
        return MCRYPT_RIJNDAEL_128;
    }

    /**
     * Is Windows?
     *
     * @return bool
     */
    protected static function isWindows()
    {
        //@startSkipCommitHooks
        return isset($_SERVER['WINDIR']) && $_SERVER['WINDIR'];
        //@finishSkipCommitHooks
    }

    /**
     * Get Windows UUID
     *
     * @return mixed
     */
    protected static function getWindowsUuid()
    {
        return `wmic csproduct get uuid`;
    }

    /**
     * Get Unix UUID
     *
     * @return null
     */
    protected static function getUnixUuid()
    {
        $unixCommands = array(
            'hostid',
            'sysctl kern.hostuuid',
            'blkid',
        );
        $uuid         = null;
        foreach ($unixCommands as $command) {
            $result = `$command`;
            if (false === strpos($uuid, 'Permission')
                && false === strpos($uuid, 'not found')
            ) {
                $uuid = $result;
                break;
            }
        }

        return $uuid;
    }

    /**
     * Get default UUID
     *
     * @return string
     */
    protected static function getDefaultUuid()
    {
        return phpversion();
    }

    /**
     * Get system UUID
     *
     * @return string
     */
    private static function getUuid()
    {
        if (null === self::$uuid) {
            if (self::isWindows()) {
                self::$uuid = self::getWindowsUuid();
            } else {
                self::$uuid = self::getUnixUuid();
                if (null === self::$uuid) {
                    self::$uuid = self::getDefaultUuid();
                }
            }
            self::$uuid = base64_encode(self::$uuid);
        }

        return self::$uuid;
    }
}
