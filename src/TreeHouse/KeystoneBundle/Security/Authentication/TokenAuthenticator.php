<?php

namespace TreeHouse\KeystoneBundle\Security\Authentication;

use Symfony\Component\Security\Http\Authentication\SimplePreAuthenticatorInterface;

class TokenAuthenticator extends AbstractTokenAuthenticator implements SimplePreAuthenticatorInterface
{
}
