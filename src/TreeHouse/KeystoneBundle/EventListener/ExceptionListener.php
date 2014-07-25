<?php

namespace TreeHouse\KeystoneBundle\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class ExceptionListener
{
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();

        if ($exception instanceof AuthenticationException) {
            $event->setResponse(new JsonResponse(['error' => $exception->getMessage()], 403));
        }

        if ($exception instanceof AccessDeniedHttpException) {
            $event->setResponse(new JsonResponse(['error' => $exception->getMessage()], 401));
        }
    }
}
