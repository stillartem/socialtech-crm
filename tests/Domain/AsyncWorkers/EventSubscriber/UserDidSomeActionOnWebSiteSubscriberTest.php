<?php

namespace App\Tests\Domain\AsyncWorkers\EventSubscriber;

use App\Domain\AsyncWorkers\Event\UserDidSomeActionOnWebSite;
use App\Domain\AsyncWorkers\EventSubscriber\UserDidSomeActionOnWebSiteSubscriber;
use App\Tests\BaseWebTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use SocialTech\StorageInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class UserDidSomeActionOnWebSiteSubscriberTest extends BaseWebTestCase
{
    /** @var UserDidSomeActionOnWebSiteSubscriber */
    private $subscriber;

    /** @var StorageInterface | MockObject */
    private $storage;

    /** @var ParameterBagInterface | MockObject */
    private $parameterBag;

    protected function setUp(): void
    {
        parent::setUp();
        $this->storage = $this->getMockBuilder(StorageInterface::class)->disableOriginalConstructor()->getMock();
        $this->parameterBag =
            $this->getMockBuilder(ParameterBagInterface::class)->disableOriginalConstructor()->getMock();

        $this->subscriber = new UserDidSomeActionOnWebSiteSubscriber($this->parameterBag, $this->storage);
    }

    public function test_it_should_put_analytic_to_storage()
    {
        $path = 'test_path';
        $name = '0.json';
        $json = '{"hello:1}';
        $this->parameterBag->expects($this->once())->method('get')->with('path_for_analytics')->willReturn($path);
        $event = $this->getMockBuilder(UserDidSomeActionOnWebSite::class)->disableOriginalConstructor()->getMock();
        $event->expects($this->once())->method('asJson')->willReturn($json);
        $this->storage->expects($this->once())->method('store')->with($path . '/' . $name, $json);
        $this->subscriber->storeAnalyticsData($event);
    }
}
