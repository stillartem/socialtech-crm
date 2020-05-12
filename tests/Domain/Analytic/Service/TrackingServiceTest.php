<?php

namespace App\Tests\Domain\Analytic\Service;

use App\Domain\Analytic\Service\TrackingService;
use App\Domain\Analytic\ValueObject\AnalyticData;
use App\Domain\AsyncWorkers\Entity\TaskEntityInterface;
use App\Domain\AsyncWorkers\Event\UserDidSomeActionOnWebSite;
use App\Domain\AsyncWorkers\ValueObject\TaskStatus;
use App\Domain\Core\ValueObject\Uuid4;
use App\Domain\RabbitMQ\Entity\ConsumedQueuedEvent;
use App\Domain\RabbitMQ\Repository\ConsumedArchivedEventRepository;
use App\Domain\RabbitMQ\Repository\ConsumedQueuedEventRepository;
use App\Tests\BaseWebTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;

class TrackingServiceTest extends BaseWebTestCase
{
    /** @var ConsumedQueuedEventRepository | MockObject */
    private $consumedQueuedEventRepository;

    /** @var ConsumedArchivedEventRepository  | MockObject */
    private $consumedArchivedEventRepository;

    /** @var LoggerInterface | MockObject */
    private $logger;

    private TrackingService $trackingService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->consumedArchivedEventRepository =
            $this->getMockBuilder(ConsumedArchivedEventRepository::class)
                ->disableOriginalConstructor()
                ->getMock();
        $this->consumedQueuedEventRepository =
            $this->getMockBuilder(ConsumedQueuedEventRepository::class)
                ->disableOriginalConstructor()
                ->getMock();
        $this->logger =
            $this->getMockBuilder(LoggerInterface::class)
                ->disableOriginalConstructor()
                ->getMock();
        $this->trackingService = new TrackingService(
            $this->consumedQueuedEventRepository, $this->consumedArchivedEventRepository, $this->logger
        );
    }

    public function test_it_should_put_event_to_queue()
    {
        $this->consumedQueuedEventRepository->expects($this->once())->method('findOneBy')->willReturn(null);
        $this->consumedArchivedEventRepository->expects($this->once())->method('findOneBy')->willReturn(null);

        $analyticData = $this->getAnalyticData();
        $this->consumedQueuedEventRepository->expects($this->once())->method('save')->with(
            $this->getConsuemedQueueRecordBaseOnAnalytic($analyticData)
        );

        $this->trackingService->putConsumedEventToQueue($analyticData);
    }

    public function test_it_should_not_put_event_to_queue()
    {
        $task = $this->getMockBuilder(TaskEntityInterface::class)->getMock();
        $this->consumedQueuedEventRepository->expects($this->once())->method('findOneBy')->willReturn($task);
        $this->consumedArchivedEventRepository->expects($this->once())->method('findOneBy')->willReturn(null);

        $analyticData = $this->getAnalyticData();
        $this->logger->expects($this->once())->method('alert');

        $this->trackingService->putConsumedEventToQueue($analyticData);
    }

    private function getConsuemedQueueRecordBaseOnAnalytic(AnalyticData $analyticData)
    {
        return (new ConsumedQueuedEvent())
            ->setMessageId($analyticData->getMessageId())
            ->setTimestamp($analyticData->getCreatedAt())
            ->setRoutingKey(UserDidSomeActionOnWebSite::EVENT_NAME)
            ->setEventContent($analyticData->asArray())
            ->setStatus(new TaskStatus(TaskStatus::FREE));
    }
}
