<?php

namespace TreeHouse\KeystoneBundle\Tests\Controller;

use Symfony\Component\HttpFoundation\Response;
use TreeHouse\KeystoneBundle\Test\WebTestCase;

class TokenControllerTest extends WebTestCase
{
    public function testGetTokenWithInvalidUsername()
    {
        $data = [
            'auth' => [
                'passwordCredentials' => [
                    'username' => 'non-existing-user',
                    'password' => static::$password,
                ]
            ]
        ];

        $response = $this->doTokenRequest($data);

        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    public function testGetTokenWithInvalidPassword()
    {
        $data = [
            'auth' => [
                'passwordCredentials' => [
                    'username' => static::$username,
                    'password' => '1234' . uniqid(),
                ]
            ]
        ];

        $response = $this->doTokenRequest($data);

        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    public function testGetTokenWithoutPostData()
    {
        $response = $this->doTokenRequest('');

        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    public function testGetTokenWithBadMethod()
    {
        $response = $this->doTokenRequest('', 'GET');

        $this->assertEquals(Response::HTTP_METHOD_NOT_ALLOWED, $response->getStatusCode());
    }

    public function testGetTokenWithValidUsernameAndPassword()
    {
        $data = [
            'auth' => [
                'passwordCredentials' => [
                    'username' => static::$username,
                    'password' => static::$password,
                ]
            ]
        ];

        $response = $this->doTokenRequest($data);
        $result   = json_decode($response->getContent(), true);

        // check for the right structure
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('access', $result);
        $this->assertInternalType('array', $result['access']);
        $this->assertArrayHasKey('token', $result['access']);
        $this->assertInternalType('array', $result['access']['token']);
        $this->assertArrayHasKey('id', $result['access']['token']);
        $this->assertInternalType('string', $result['access']['token']['id']);

        // check for user info
        $this->assertArrayHasKey('user', $result['access']);
        $this->assertInternalType('array', $result['access']['user']);
        $this->assertArrayHasKey('id', $result['access']['user']);
        $this->assertArrayHasKey('username', $result['access']['user']);
        $this->assertEquals(static::$username, $result['access']['user']['username']);

        // check that we have a service catalog (only the api, test user does not have rights to the super secret service)
        $this->assertArrayHasKey('serviceCatalog', $result['access']);
        $this->assertInternalType('array', $result['access']['serviceCatalog']);
        $this->assertCount(1, $result['access']['serviceCatalog']);
        $this->assertEquals('api', $result['access']['serviceCatalog'][0]['name']);
        $this->assertEquals('compute', $result['access']['serviceCatalog'][0]['type']);

        $endpoints = [
            ['adminUrl' => 'http://example.org', 'publicUrl' => 'http://example.org']
        ];
        $this->assertEquals($endpoints, $result['access']['serviceCatalog'][0]['endpoints']);
    }

    public function testNonAuthenticatedRequest()
    {
        $this->client->request('GET', $this->getRoute('test_api'));
        $response = $this->client->getResponse();

        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    public function testAuthenticatedRequest()
    {
        $this->requestWithValidToken('GET', $this->getRoute('test_api'));
        $response = $this->client->getResponse();

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals(['ok' => 'it works!'], json_decode($response->getContent(), true));
    }

    public function testAuthenticatedExpiredTokenRequest()
    {
        $tokenId = $this->requestToken()['access']['token']['id'];

        // expire token in database
        $doctrine = static::$kernel->getContainer()->get('doctrine');
        $token = $doctrine->getRepository('TreeHouseKeystoneBundle:Token')->find($tokenId);
        $token->setExpiresAt(new \DateTime('-1 day'));
        $doctrine->getManager()->flush();

        // perform request with token that is now expired
        $this->client->request(
            'GET',
            $this->getRoute('test_api'),
            [],
            [],
            ['HTTP_X-Auth-Token' => $tokenId],
            null,
            true
        );
        $response = $this->client->getResponse();

        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
        $this->assertEquals(['error' => 'Token expired'], json_decode($response->getContent(), true));
    }

    public function testInvalidTokenRequest()
    {
        $tokenId = 'invalid-token';

        $this->client->request(
            'GET',
            $this->getRoute('test_api'),
            [],
            [],
            ['HTTP_X-Auth-Token' => $tokenId],
            null,
            true
        );
        $response = $this->client->getResponse();

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertEquals(['error' => 'Authentication Failed'], json_decode($response->getContent(), true));
    }

    /**
     * @param string|array $data
     * @param string       $method
     *
     * @return Response
     */
    protected function doTokenRequest($data, $method = 'POST')
    {
        $this->client->request($method, $this->getRoute('get_token'), [], [], [], json_encode($data));

        return $this->client->getResponse();
    }
}
