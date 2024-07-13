<?php

namespace App\EventListener;

use App\Exception\CustomHttpException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class ExceptionListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if ($exception instanceof CustomHttpException) {
            $response = new JsonResponse($exception->toArray(), $exception->getStatusCode());
        } elseif ($exception instanceof HttpExceptionInterface) {
            $response = new JsonResponse([
                'code' => $exception->getStatusCode(),
                'message' => $exception->getMessage()
            ], $exception->getStatusCode());
        } else {
            $response = new JsonResponse([
                'code' => JsonResponse::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'An unexpected error occurred.'
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        // Set the new response to the event
        $event->setResponse($response);
    }
}
