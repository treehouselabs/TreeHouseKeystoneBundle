<?php

namespace TreeHouse\KeystoneBundle\Tests\Security\Http;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use TreeHouse\KeystoneBundle\Security\Http\KeystoneEntryPoint;

class KeystoneEntryPointTest extends \PHPUnit_Framework_TestCase
{
    public function testStart()
    {
        $request = $this->prophesize(Request::class);
        $entryPoint = new KeystoneEntryPoint();
        $authException = new AuthenticationException('The exception message');

        $this->assertInstanceOf(AuthenticationEntryPointInterface::class, $entryPoint);

        $response = $entryPoint->start($request->reveal(), $authException);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(401, $response->getStatusCode());
    }

    public function testStartWithoutException()
    {
        $request = $this->prophesize(Request::class);
        $entryPoint = new KeystoneEntryPoint();

        $this->assertInstanceOf(AuthenticationEntryPointInterface::class, $entryPoint);

        $response = $entryPoint->start($request->reveal());

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(401, $response->getStatusCode());
    }
}
