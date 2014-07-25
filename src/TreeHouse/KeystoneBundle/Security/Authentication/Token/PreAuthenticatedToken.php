<?php

namespace TreeHouse\KeystoneBundle\Security\Authentication\Token;

use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken as BaseToken;
use Symfony\Component\Security\Core\Role\RoleInterface;

class PreAuthenticatedToken extends BaseToken
{
    /**
     * @var array|RoleInterface[]
     */
    protected $token;

    /**
     * @param array|RoleInterface[] $token
     * @param string                $providerKey
     * @param array                 $roles
     */
    public function __construct($token, $providerKey, array $roles = [])
    {
        parent::__construct($token, $token, $providerKey, $roles);

        $this->token = $token;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return (string) $this->token;
    }
}
