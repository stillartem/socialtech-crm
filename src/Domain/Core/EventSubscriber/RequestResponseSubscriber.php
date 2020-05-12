<?php

namespace App\Domain\Core\EventSubscriber;

use App\Port\Controller\RestControllerInterface;
use Exception;
use function is_array;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Throwable;

class RequestResponseSubscriber implements EventSubscriberInterface
{
    /** @var LoggerInterface */
    private LoggerInterface $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }


    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }


    /**
     * @param FilterResponseEvent $event
     *
     * @throws Exception
     */
    public function onKernelResponse(FilterResponseEvent $event): void
    {
        $controller = $this->retrieveController($event->getRequest());

        if (!is_subclass_of($controller, RestControllerInterface::class) || !$event->isMasterRequest()) {
            return;
        }

        $this->logRequestAndResult($event->getRequest(), $event->getResponse());
    }


    /**
     * @param Request $request
     * @param Response $response
     *
     * @throws Exception
     */
    private function logRequestAndResult(Request $request, Response $response): void
    {
        $this->logger->info(
            'Api call',
            [
                'action' => $request->attributes->get('_controller'),
                'code' => $response->getStatusCode(),
                'request' => $request->attributes->get('_route_params'),
                'response' => !empty($response->getContent()) ? json_decode($response->getContent(), true) : null,
            ]
        );
    }


    /**
     * @param Request $request
     *
     * @return AbstractController|null
     */
    private function retrieveController(Request $request): ?string
    {
        try {
            $controller = $request->attributes->get('_controller');
            if ($controller === null) {
                return null;
            }
            if (is_array($controller)) {
                return $controller[0];
            }
            $controller = explode('::', $controller, 2);

            $classname = $controller[0];
        } catch (Throwable $exception) {
            $this->logger->emergency($exception->getMessage());

            return null;
        }
        if (!class_exists($classname)) {
            return null;
        }

        return $classname;
    }
}
