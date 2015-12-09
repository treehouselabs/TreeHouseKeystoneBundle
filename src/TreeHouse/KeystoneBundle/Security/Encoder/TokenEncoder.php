<?php

namespace TreeHouse\KeystoneBundle\Security\Encoder;

class TokenEncoder
{
    const HASH_DELIMITER = ':';

    /**
     * @var string
     */
    protected $secret;

    /**
     * @param string $secret
     */
    public function __construct($secret)
    {
        $this->secret = $secret;
    }

    /**
     * @param string $hash
     *
     * @return array
     */
    public function decodeHash($hash)
    {
        return explode(self::HASH_DELIMITER, base64_decode($hash));
    }

    /**
     * @param array $parts
     *
     * @return string
     */
    public function encodeHash(array $parts)
    {
        return base64_encode(implode(self::HASH_DELIMITER, $parts));
    }

    /**
     * @param string $class
     * @param string $username
     * @param string $password
     * @param int    $expires
     *
     * @return string
     */
    public function generateHash($class, $username, $password, $expires)
    {
        return hash('sha256', $class . $username . $password . $expires . $this->secret);
    }

    /**
     * @param string $hash1
     * @param string $hash2
     *
     * @return bool
     *
     * @see StringUtils::equals()
     */
    public function compareHashes($hash1, $hash2)
    {
        return hash_equals($hash1, $hash2);
    }

    /**
     * @param string $class
     * @param string $username
     * @param string $password
     * @param int    $expires
     *
     * @return string
     */
    public function generateTokenValue($class, $username, $password, $expires)
    {
        return $this->encodeHash([
            $class,
            base64_encode($username),
            $expires,
            $this->generateHash($class, $username, $password, $expires),
        ]);
    }
}
