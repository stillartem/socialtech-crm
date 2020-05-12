<?php

namespace App\Tests\Domain\Analytic\EventSubscriber;

use App\Domain\Analytic\Event\TrackAnalytic;
use App\Domain\Analytic\EventSubscriber\TrackAnalyticSubscriber;
use App\Domain\Analytic\Service\TrackingService;
use App\Domain\Analytic\ValueObject\AnalyticData;
use App\Tests\BaseWebTestCase;
use PHPUnit\Framework\MockObject\MockObject;

class TrackAnalyticSubscriberTest extends BaseWebTestCase
{
    /** @var TrackingService  | MockObject */
    private $trackingService;

    private TrackAnalyticSubscriber $trackingSubscriber;

    protected function setUp(): void
    {
        parent::setUp();
        $this->trackingService = $this->getMockBuilder(TrackingService::class)->disableOriginalConstructor()->getMock();
        $this->trackingSubscriber = new TrackAnalyticSubscriber($this->trackingService);
    }

    public function test_it_should_track_analytic_data()
    {
        $analyticData = $this->getAnalyticData();
        $this->trackingService->expects($this->once())->method('putConsumedEventToQueue')->with($analyticData);
        $this->trackingSubscriber->trackAnalytic($this->getTrackAnalyticEvent($analyticData));
    }

    private function getTrackAnalyticEvent(AnalyticData $analyticData)
    {
        return TrackAnalytic::fromScalar(
            (string)$analyticData->getUserId(), $analyticData->getSourceLabel(), $analyticData->getMessageId(),
            $analyticData->getCreatedAt()->format('d-m-Y')
        );
    }
}
