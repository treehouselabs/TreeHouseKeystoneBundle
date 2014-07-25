<?php

namespace TreeHouse\KeystoneBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use TreeHouse\KeystoneBundle\Manager\ServiceManager;
use TreeHouse\KeystoneBundle\Manager\TokenManager;
use TreeHouse\KeystoneBundle\Model\Service;
use TreeHouse\KeystoneBundle\Model\Token;

class TokenController extends Controller
{
    /**
     * @return JsonResponse
     */
    public function createAction()
    {
        $provider = $this->getUserProvider();

        // load user and check it's the right class
        if (!($user = $this->getUser()) || !$provider->supportsClass(get_class($user))) {
            return new JsonResponse(['error' => 'Unauthorized'], 401);
        }

        $manager = $this->getTokenManager();
        $token   = $manager->createToken($user, 3600);

        $data = [
            'access' => [
                'token'          => $this->getTokenData($token),
                'user'           => $this->getUserData($user),
                'serviceCatalog' => $this->getServiceCatalog($token)
            ]
        ];

        return new JsonResponse($data, 200, ['Vary' => 'X-Auth-Token']);
    }

    /**
     * @param Token $token
     *
     * @return array
     */
    protected function getTokenData(Token $token)
    {
        return [
            'id'      => $token->getId(),
            'expires' => $token->getExpiresAt()->format(\DateTime::ISO8601)
        ];
    }

    /**
     * @return array
     */
    protected function getServiceCatalog()
    {
        $services = $this->getUserServices();

        $catalog = [];
        foreach ($services as $service) {
            $endpoints = [];
            foreach ($service->getEndpoints() as $endpoint) {
                $endpoints[] = [
                    'adminUrl'  => $endpoint->getAdminUrl(),
                    'publicUrl' => $endpoint->getPublicUrl()
                ];
            }

            $catalog[] = [
                'name' => $service->getName(),
                'type' => $service->getType(),
                'endpoints' => $endpoints,
            ];
        }

        return $catalog;
    }

    /**
     * @return Service[]
     */
    protected function getUserServices()
    {
        /** @var SecurityContextInterface $securityContext */
        $securityContext = $this->container->get('security.context');

        // filter out services that the user is not granted
        return array_filter(
            $this->getServiceManager()->getServices(),
            function (Service $service) use ($securityContext) {
                $grants = $service->getGrants();

                return !empty($grants) && !$securityContext->isGranted($grants);
            }
        );
    }

    /**
     * @param UserInterface $user
     *
     * @return array
     */
    protected function getUserData(UserInterface $user)
    {
        return [
            'id'       => $this->getUserId($user),
            'username' => $user->getUsername(),
        ];
    }

    /**
     * Returns the user identifier.
     * If this is a single identifier, that value is returned.
     * Otherwise all the identifiers are returned as an array.
     *
     * @param UserInterface $user
     *
     * @return array|mixed
     */
    protected function getUserId(UserInterface $user)
    {
        $class = $this->container->getParameter('tree_house.keystone.model.user.class');
        $meta = $this->getDoctrine()->getManagerForClass($class)->getClassMetadata($class);

        $ids = $meta->getIdentifierValues($user);
        if (sizeof($ids) === 1) {
            return current($ids);
        }

        return $ids;
    }

    /**
     * @return UserProviderInterface
     */
    protected function getUserProvider()
    {
        return $this->get('tree_house.keystone.user_provider');
    }

    /**
     * @return TokenManager
     */
    protected function getTokenManager()
    {
        return $this->get('tree_house.keystone.token_manager');
    }
    /**
     * @return ServiceManager
     */
    protected function getServiceManager()
    {
        return $this->get('tree_house.keystone.service_manager');
    }
}
