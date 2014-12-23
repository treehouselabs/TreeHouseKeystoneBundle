<?php

namespace TreeHouse\KeystoneBundle\Tests\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use TreeHouse\KeystoneBundle\EventListener\ExceptionListener;

class ExceptionListenerTest extends \PHPUnit_Framework_TestCase
{
    public function testAuthenticationException()
    {
        $event = $this->createEventMock();

        // mock an authentication exception
        $event
            ->expects($this->once())
            ->method('getException')
            ->will($this->returnValue(new AuthenticationException()))
        ;

        $listener = new ExceptionListener();
        $listener->onKernelException($event);

        // should now receive a 403 response
        $response = $event->getResponse();
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(JsonResponse::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    public function testAccessDeniedException()
    {
        $event = $this->createEventMock();

        // mock an authentication exception
        $event
            ->expects($this->once())
            ->method('getException')
            ->will($this->returnValue(new AccessDeniedHttpException()))
        ;

        $listener = new ExceptionListener();
        $listener->onKernelException($event);

        // should now receive a 403 response
        $response = $event->getResponse();
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(JsonResponse::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|GetResponseForExceptionEvent
     */
    protected function createEventMock()
    {
        return $this
            ->getMockBuilder(GetResponseForExceptionEvent::class)
            ->disableOriginalConstructor()
            ->setMethods(['getException'])
            ->getMock()
        ;
    }
}
