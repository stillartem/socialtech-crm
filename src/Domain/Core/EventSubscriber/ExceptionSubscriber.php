<?php

namespace App\Domain\Core\EventSubscriber;

use App\Domain\Core\Exception\BadDataException;
use App\Domain\Core\Exception\BusinessExceptionInterface;
use App\Domain\Core\Exception\ErrorCodeExceptionInterface;
use App\Domain\SocialTechCustomer\Exception\AuthTokenIsWrongException;
use App\Domain\SocialTechCustomer\Exception\CustomerAlreadyExistException;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * for debugging you may use:
 * php bin/console debug:event-dispatcher
 */
class ExceptionSubscriber implements EventSubscriberInterface
{
    public const INTERNAL_ERRROR = 'internal.error';

    public const INVALID_ARGUMENT = 'invalid.argument';

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;


    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }


    /**
     * return the subscribed events, their methods and priorities
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => [
                ['logException', 20],
                ['returnFormattedResponse', -10],
            ],
        ];
    }


    /**
     * @param ExceptionEvent $event
     *
     * @throws \Exception
     */
    public function returnFormattedResponse(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $response = $this->getResponseWithException($exception);

        $event->setResponse($response);
    }


    /**
     * @param ExceptionEvent $event
     */
    public function logException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        switch (true) {
            case $exception instanceof BusinessExceptionInterface:
                return;
            default:
                $this->logger->error(
                    $exception->getMessage(),
                    [
                        'class' => get_class($exception),
                        'trace' => $exception->getMessage(),
                    ]
                );
        }
    }

    /**
     * @param \Throwable $e
     *
     * @return JsonResponse
     */
    public function getResponseWithException(\Throwable $e): JsonResponse
    {
        switch (true) {
            case $e instanceof NotFoundHttpException:
                $code = 'page_not_found';
                $status = Response::HTTP_NOT_FOUND;
                break;
            case $e instanceof ErrorCodeExceptionInterface:
                $code = $e->getErrorCode();
                $status = Response::HTTP_BAD_REQUEST;
                break;
            case $e instanceof BadDataException:
                $code = $e->getErrorCode();
                $status = Response::HTTP_BAD_REQUEST;
                break;
            case $e instanceof AuthTokenIsWrongException:
                $code = AuthTokenIsWrongException::WRONG_AUTH_TOKEN;
                $status = Response::HTTP_FORBIDDEN;
                break;
            case $e instanceof CustomerAlreadyExistException:
                $code = $e->getErrorCode();
                $status = Response::HTTP_BAD_REQUEST;
                break;
            case $e instanceof \InvalidArgumentException:
            case $e instanceof BusinessExceptionInterface:
                $code = self::INVALID_ARGUMENT;
                $status = Response::HTTP_BAD_REQUEST;
                break;
            default:
                $code = self::INTERNAL_ERRROR;
                $status = Response::HTTP_INTERNAL_SERVER_ERROR;
        }
        $data = [
            'code' => $code,
            'status' => $status,
        ];

        return new JsonResponse($data, $status);
    }
}
