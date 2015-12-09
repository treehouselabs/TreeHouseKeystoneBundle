<?php

namespace TreeHouse\KeystoneBundle\Security\Firewall;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\AuthenticationServiceException;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;

class HttpPostAuthenticationListener implements ListenerInterface
{
    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var AuthenticationManagerInterface
     */
    protected $authenticationManager;

    /**
     * @var string
     */
    protected $providerKey;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param TokenStorageInterface          $tokenStorage
     * @param AuthenticationManagerInterface $authenticationManager
     * @param string                         $providerKey
     * @param LoggerInterface                $logger
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        AuthenticationManagerInterface $authenticationManager,
        $providerKey,
        LoggerInterface $logger = null
    ) {
        if (empty($providerKey)) {
            throw new \InvalidArgumentException('$providerKey must not be empty.');
        }

        $this->tokenStorage = $tokenStorage;
        $this->authenticationManager = $authenticationManager;
        $this->providerKey = $providerKey;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     *
     * @see http://docs.openstack.org/api/openstack-identity-service/2.0/content/POST_authenticate_v2.0_tokens_Admin_API_Service_Developer_Operations-d1e1356.html
     */
    public function handle(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if ($request->getMethod() !== 'POST') {
            throw new MethodNotAllowedHttpException(['POST']);
        }

        $data = json_decode($request->getContent(), true);

        if (null === $token = $this->createToken($data)) {
            throw new AuthenticationServiceException('Invalid JSON!');
        }

        if (null !== $this->logger) {
            $this->logger->info(
                sprintf('Post Authentication body found using passwordCredentials for user "%s"', $token->getUsername())
            );
        }

        try {
            $token = $this->authenticationManager->authenticate($token);
            $this->tokenStorage->setToken($token);
        } catch (AuthenticationException $failed) {
            $this->tokenStorage->setToken(null);

            if (null !== $this->logger) {
                $this->logger->info(
                    sprintf(
                        'Authentication request failed for user "%s" using POST and data: %s',
                        json_encode($data),
                        $failed->getMessage()
                    )
                );
            }

            throw $failed;
        }
    }

    /**
     * Parses JSON data and creates a username/password token.
     *
     * @param array $data
     *
     * @return UsernamePasswordToken|null
     */
    protected function createToken($data)
    {
        if (empty($data) || !is_array($data)) {
            return null;
        }

        if (!isset($data['auth']) || !is_array($data['auth'])) {
            return null;
        }

        if (!isset($data['auth']['passwordCredentials']) || !is_array($data['auth']['passwordCredentials'])) {
            return null;
        }

        $credentials = $data['auth']['passwordCredentials'];

        if (!isset($credentials['username']) || !isset($credentials['password'])) {
            return null;
        }

        // validate using Username and password
        $username = $credentials['username'];
        $password = $credentials['password'];

        return new UsernamePasswordToken($username, $password, $this->providerKey);
    }
}
