<?php

namespace TreeHouse\KeystoneIntegrationBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;

class ApiController
{
    public function testAction()
    {
        return new JsonResponse(['ok' => 'it works!']);
    }
}
