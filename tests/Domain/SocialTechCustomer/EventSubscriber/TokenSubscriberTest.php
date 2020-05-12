<?php

namespace App\Tests\Domain\SocialTechCustomer\EventSubscriber;

use App\Domain\SocialTechCustomer\EventSubscriber\TokenSubscriber;
use App\Domain\SocialTechCustomer\Exception\AuthTokenIsWrongException;
use App\Domain\SocialTechCustomer\Repository\AuthTokenRepository;
use App\Port\Controller\TokenAuthenticatedController;
use App\Tests\BaseWebTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;

class TokenSubscriberTest extends BaseWebTestCase
{
    /** @var TokenSubscriber */
    private $subscriber;

    /** @var AuthTokenRepository | MockObject */
    private $authTokenRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authTokenRepository =
            $this->getMockBuilder(AuthTokenRepository::class)->disableOriginalConstructor()->getMock();
        $this->subscriber = new TokenSubscriber($this->authTokenRepository);
    }

    public function test_it_should_has_correct_token()
    {
        $event = $this->getControllerEvent();
        $this->authTokenRepository->expects($this->once())->method('validateToken')->willReturn(true);
        $this->subscriber->onKernelController($event);
    }

    public function test_token_is_wrong()
    {
        $event = $this->getControllerEvent();
        $this->authTokenRepository->expects($this->once())->method('validateToken')->willReturn(false);

        $this->expectException(AuthTokenIsWrongException::class);
        $this->subscriber->onKernelController($event);
    }

    private function getControllerEvent()
    {
        $headerMock =
            $this->getMockBuilder(HeaderBag::class)->disableOriginalConstructor()->getMock();
        $headerMock->expects($this->atLeastOnce())->method('get')->with(TokenAuthenticatedController::TOKEN_KEY)
            ->willReturn('test_token');

        $request = new Request();
        $request->headers = $headerMock;
        $event = $this->getMockBuilder(ControllerEvent::class)->disableOriginalConstructor()->getMock();
        $controller =
            $this->getMockBuilder(TokenAuthenticatedController::class)->disableOriginalConstructor()->getMock();
        $сallableController = [$controller];
        $event->expects($this->atLeastOnce())->method('getController')->willReturn($сallableController);
        $event->expects($this->atLeastOnce())->method('getRequest')->willReturn($request);

        return $event;
    }
}
