<?php

namespace App\Domain\SocialTechCustomer\EventSubscriber;

use App\Domain\SocialTechCustomer\Exception\AuthTokenIsWrongException;
use App\Domain\SocialTechCustomer\Repository\AuthTokenRepository;
use App\Port\Controller\TokenAuthenticatedController;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use function is_array;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

class TokenSubscriber implements EventSubscriberInterface
{
    /** @var AuthTokenRepository */
    private AuthTokenRepository $authTokenRepository;

    /**
     * @param AuthTokenRepository $authTokenRepository
     */
    public function __construct(AuthTokenRepository $authTokenRepository)
    {
        $this->authTokenRepository = $authTokenRepository;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }


    /**
     * @param ControllerEvent $event
     *
     * @throws NonUniqueResultException
     */
    public function onKernelController(ControllerEvent $event): void
    {

        $controller = $event->getController();
        if (!is_array($controller) || !($controller[0] instanceof TokenAuthenticatedController)) {
            return;
        }
        $this->checkUserAccessToProject($event);

    }


    /**
     * @param ControllerEvent $event
     *
     * @return void
     * @throws NonUniqueResultException
     */
    private function checkUserAccessToProject(ControllerEvent $event): void
    {
        $token = $event->getRequest()->headers->get(TokenAuthenticatedController::TOKEN_KEY, null);

        if ($token === null || !$this->authTokenRepository->validateToken($token)) {
            throw AuthTokenIsWrongException::WrongTokenException();
        }
    }
}
