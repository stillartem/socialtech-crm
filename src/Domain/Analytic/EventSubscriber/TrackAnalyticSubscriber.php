<?php

namespace App\Domain\Analytic\EventSubscriber;

use App\Domain\Analytic\Event\TrackAnalytic;
use App\Domain\Analytic\Service\TrackingService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TrackAnalyticSubscriber implements EventSubscriberInterface
{
    /** @var TrackingService */
    private TrackingService $trackingService;

    public function __construct(TrackingService $trackingService)
    {
        $this->trackingService = $trackingService;
    }

    public static function getSubscribedEvents()
    {
        return [
            TrackAnalytic::class => [
                ['trackAnalytic', 10],
            ],
        ];
    }

    /**
     * @param TrackAnalytic $analytic
     *
     * @throws \Exception
     */
    public function trackAnalytic(TrackAnalytic $analytic): void
    {
        $this->trackingService->putConsumedEventToQueue($analytic->getAnalyticData());
    }
}
