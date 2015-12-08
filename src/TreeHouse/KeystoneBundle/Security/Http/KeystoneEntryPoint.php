<?php

namespace TreeHouse\KeystoneBundle\Security\Http;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

class KeystoneEntryPoint implements AuthenticationEntryPointInterface
{
    /**
     * @inheritdoc
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        $response = new JsonResponse();
        $response->setStatusCode(401);

        $response->setData(['ok' => false, 'error' => 'Accessing this resource requires authorization']);

        return $response;
    }
}
