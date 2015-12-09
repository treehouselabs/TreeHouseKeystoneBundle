<?php

namespace TreeHouse\KeystoneBundle\Security\Authentication;

use Symfony\Component\Security\Core\Authentication\SimplePreAuthenticatorInterface;

/**
 * Needed for Symfony 2.6 and 2.7 support.
 */
class LegacyTokenAuthenticator extends AbstractTokenAuthenticator implements SimplePreAuthenticatorInterface
{
}
