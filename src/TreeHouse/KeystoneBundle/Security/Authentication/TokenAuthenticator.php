<?php

namespace TreeHouse\KeystoneBundle\Security\Authentication;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\SimplePreAuthenticatorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\AuthenticationServiceException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use TreeHouse\KeystoneBundle\Manager\TokenManager;
use TreeHouse\KeystoneBundle\Model\Token;
use TreeHouse\KeystoneBundle\Security\Authentication\Token\PreAuthenticatedToken;

class TokenAuthenticator implements SimplePreAuthenticatorInterface, AuthenticationFailureHandlerInterface
{
    /**
     * @var TokenManager
     */
    protected $tokenManager;

    /**
     * @var UserCheckerInterface
     */
    protected $userChecker;

    /**
     * @param TokenManager         $tokenManager
     * @param UserCheckerInterface $userChecker
     */
    public function __construct(TokenManager $tokenManager, UserCheckerInterface $userChecker)
    {
        $this->tokenManager = $tokenManager;
        $this->userChecker  = $userChecker;
    }

    /**
     * @inheritdoc
     */
    public function supportsToken(TokenInterface $token, $providerKey)
    {
        return $token instanceof PreAuthenticatedToken && $token->getProviderKey() === $providerKey;
    }

    /**
     * @inheritdoc
     */
    public function createToken(Request $request, $providerKey)
    {
        if (!$request->headers->has('X-Auth-Token')) {
            throw new BadCredentialsException('No token found');
        }

        $authToken = (string) $request->headers->get('X-Auth-Token');

        return new PreAuthenticatedToken($authToken, $providerKey);
    }

    /**
     * @inheritdoc
     */
    public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey)
    {
        /** @var PreAuthenticatedToken $token */
        $authToken = $token->getToken();
        if (empty($authToken)) {
            $authToken = 'NONE_PROVIDED';
        }

        $tokenEntity = $this->tokenManager->findById($authToken);
        if (!$tokenEntity) {
            throw new BadCredentialsException('Bad token');
        }

        if (false === $this->tokenManager->validate($tokenEntity)) {
            throw new AuthenticationException('Token not valid');
        }

        $user = $this->retrieveUser($userProvider, $tokenEntity);

        if (!$user instanceof UserInterface) {
            throw new AuthenticationServiceException('retrieveUser() must return a UserInterface.');
        }

        try {
            $this->userChecker->checkPreAuth($user);
            $this->checkAuthentication($user, $tokenEntity, $token);
            $this->userChecker->checkPostAuth($user);
        } catch (BadCredentialsException $e) {
            throw new BadCredentialsException('Bad credentials', 0, $e);
        }

        $authenticatedToken = new PreAuthenticatedToken($token->getToken(), $providerKey, $user->getRoles());
        $authenticatedToken->setUser($user);
        $authenticatedToken->setAttributes($token->getAttributes());

        return $authenticatedToken;
    }

    /**
     * @inheritdoc
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return new JsonResponse(['error' => 'Authentication Failed.'], 403);
    }

    /**
     * @param UserInterface         $user
     * @param Token                 $tokenEntity
     * @param PreAuthenticatedToken $token
     *
     * @throws BadCredentialsException
     */
    protected function checkAuthentication(UserInterface $user, Token $tokenEntity, PreAuthenticatedToken $token)
    {
        $currentUser = $token->getUser();
        if ($currentUser instanceof UserInterface) {
            if ($currentUser->getPassword() !== $user->getPassword()) {
                throw new BadCredentialsException('The credentials were changed from another session.');
            }
        } else {
            if ("" === ($presentedToken = $token->getToken())) {
                throw new BadCredentialsException('The presented token cannot be empty.');
            }

            list ($class, $username, $expires, $hash) = $this->tokenManager->getEncoder()->decodeHash($tokenEntity->getHash());

            $username = base64_decode($username, true);

            $hash2 = $this->tokenManager->getEncoder()->generateHash($class, $username, $user->getPassword(), $expires);
            if (false === $this->tokenManager->getEncoder()->compareHashes($hash, $hash2)) {
                throw new BadCredentialsException('The presented token is invalid.');
            }
        }
    }

    /**
     * @param UserProviderInterface $userProvider
     * @param Token                 $token
     *
     * @throws AuthenticationException
     * @throws AuthenticationServiceException
     * @return UserInterface
     */
    protected function retrieveUser(UserProviderInterface $userProvider, Token $token)
    {
        $parts = $this->tokenManager->getEncoder()->decodeHash($token->getHash());

        if (count($parts) !== 4) {
            throw new AuthenticationException('The hash is invalid.');
        }

        list ($class, $username, $expires, $hash) = $parts;

        if (false === $username = base64_decode($username, true)) {
            throw new AuthenticationException('$username contains a character from outside the base64 alphabet.');
        }

        try {
            $user = $userProvider->loadUserByUsername($username);
        } catch (\Exception $e) {
            throw new AuthenticationServiceException($e->getMessage(), 0, $e);
        }

        if (!$user instanceof UserInterface) {
            throw new AuthenticationServiceException('The user provider must return a UserInterface object.');
        }

        return $user;
    }
}
