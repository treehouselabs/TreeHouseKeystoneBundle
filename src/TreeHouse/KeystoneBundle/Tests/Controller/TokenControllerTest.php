<?php

namespace TreeHouse\KeystoneBundle\Tests\Controller;

use Symfony\Component\HttpFoundation\Response;
use TreeHouse\KeystoneBundle\Test\WebTestCase;

class TokenControllerTest extends WebTestCase
{
    const HTTP_FORBIDDEN          = 403;
    const HTTP_METHOD_NOT_ALLOWED = 405;

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

        $this->assertEquals(self::HTTP_FORBIDDEN, $response->getStatusCode());
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

        $this->assertEquals(self::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    public function testGetTokenWithoutPostData()
    {
        $response = $this->doTokenRequest('');

        $this->assertEquals(self::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    public function testGetTokenWithBadMethod()
    {
        $response = $this->doTokenRequest('', 'GET');

        $this->assertEquals(self::HTTP_METHOD_NOT_ALLOWED, $response->getStatusCode());
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

        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('access', $result);
        $this->assertArrayHasKey('token', $result['access']);
        $this->assertArrayHasKey('id', $result['access']['token']);

        return $result['access']['token']['id'];
    }

    public function testNonAuthenticatedRequestWithToken()
    {
        $this->client->request('GET', $this->getRoute('test_api'));
        $repsonse = $this->client->getResponse();

        $this->assertEquals(403, $repsonse->getStatusCode());
    }

    public function testAuthenticatedRequestWithToken()
    {
        $this->requestWithValidToken('GET', $this->getRoute('test_api'));
        $repsonse = $this->client->getResponse();

        $this->assertEquals(200, $repsonse->getStatusCode());
        $this->assertEquals(['ok' => 'it works!'], json_decode($repsonse->getContent(), true));
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
