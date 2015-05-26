<?php

namespace TreeHouse\KeystoneBundle\Manager;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectRepository;
use TreeHouse\KeystoneBundle\Entity\Token;
use TreeHouse\KeystoneBundle\Security\Encoder\TokenEncoder;
use Symfony\Component\Security\Core\User\UserInterface;

class TokenManager
{
    /**
     * @var TokenEncoder
     */
    protected $encoder;

    /**
     * @var ManagerRegistry
     */
    protected $doctrine;

    /**
     * Constructor.
     *
     * @param TokenEncoder    $encoder
     * @param ManagerRegistry $doctrine
     */
    public function __construct(TokenEncoder $encoder, ManagerRegistry $doctrine)
    {
        $this->encoder  = $encoder;
        $this->doctrine = $doctrine;
    }

    /**
     * Returns a token instance
     *
     * @param UserInterface $user
     * @param integer       $ttl
     *
     * @return Token
     */
    public function createToken($user, $ttl = 3600)
    {
        $expires = time() + (int) $ttl;

        $hash = $this->getEncoder()->generateTokenValue(
            get_class($user),
            $user->getUsername(),
            $user->getPassword(),
            $expires
        );

        $token = new Token();
        $token->setHash($hash);
        $token->setExpiresAt(new \DateTime('@' . $expires));

        $this->updateToken($token);

        return $token;
    }

    /**
     * @param $criteria
     *
     * @return Token|null
     */
    public function findOneBy($criteria)
    {
        return $this->getRepository()->findOneBy($criteria);
    }

    /**
     * Finds a token by id (which is itself a token)
     *
     * @param string $token
     *
     * @return Token|null
     */
    public function findById($token)
    {
        return $this->getRepository()->find($token);
    }

    /**
     * Updates a Token.
     *
     * @param Token   $token
     * @param Boolean $andFlush Whether to flush the changes (default true)
     */
    public function updateToken(Token $token, $andFlush = true)
    {
        $this->doctrine->getManager()->persist($token);
        if ($andFlush) {
            $this->doctrine->getManager()->flush();
        }
    }

    /**
     * @param Token $token
     *
     * @deprecated use $this->isExpired instead
     *
     * @return boolean
     */
    public function validate(Token $token)
    {
        return $this->isExpired($token);
    }

    /**
     * @param Token $token
     *
     * @return bool
     */
    public function isExpired(Token $token)
    {
        return new \DateTime() < $token->getExpiresAt();
    }

    /**
     * @return TokenEncoder
     */
    public function getEncoder()
    {
        return $this->encoder;
    }

    /**
     * @return ObjectRepository
     */
    protected function getRepository()
    {
        return $this->doctrine->getRepository('TreeHouseKeystoneBundle:Token');
    }
}
