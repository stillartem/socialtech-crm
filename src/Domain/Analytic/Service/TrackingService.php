<?php

namespace App\Domain\Analytic\Service;

use App\Domain\Analytic\ValueObject\AnalyticData;
use App\Domain\AsyncWorkers\Event\UserDidSomeActionOnWebSite;
use App\Domain\AsyncWorkers\ValueObject\TaskStatus;
use App\Domain\RabbitMQ\Entity\ConsumedQueuedEvent;
use App\Domain\RabbitMQ\Repository\ConsumedArchivedEventRepository;
use App\Domain\RabbitMQ\Repository\ConsumedQueuedEventRepository;
use Psr\Log\LoggerInterface;

class TrackingService
{
    /** @var ConsumedQueuedEventRepository */
    private ConsumedQueuedEventRepository $consumedQueuedEventRepository;

    /** @var ConsumedArchivedEventRepository */
    private ConsumedArchivedEventRepository $consumedArchivedEventRepository;

    /** @var LoggerInterface */
    private LoggerInterface $logger;

    public function __construct(
        ConsumedQueuedEventRepository $consumedQueuedEventRepository,
        ConsumedArchivedEventRepository $consumedArchivedEventRepository,
        LoggerInterface $logger
    ) {
        $this->consumedQueuedEventRepository = $consumedQueuedEventRepository;
        $this->consumedArchivedEventRepository = $consumedArchivedEventRepository;
        $this->logger = $logger;
    }

    /**
     * @param AnalyticData $analyticData
     *
     * @throws \Exception
     */
    public function putConsumedEventToQueue(AnalyticData $analyticData): void
    {
        $consumedEventArchiveRecord =
            $this->consumedArchivedEventRepository->findOneBy(['messageId' => $analyticData->getMessageId()]);
        $consumedEventQueueRecord =
            $this->consumedQueuedEventRepository->findOneBy(['messageId' => $analyticData->getMessageId()]);

        if ($consumedEventArchiveRecord === null && $consumedEventQueueRecord === null) {

            $consumedEventQueueRecord = (new ConsumedQueuedEvent())
                ->setMessageId($analyticData->getMessageId())
                ->setTimestamp($analyticData->getCreatedAt())
                ->setRoutingKey(UserDidSomeActionOnWebSite::EVENT_NAME)
                ->setEventContent($analyticData->asArray())
                ->setStatus(new TaskStatus(TaskStatus::FREE));


            $this->logger->info('Analytic data putted to queue', ['data' => $analyticData->asArray()]);
        } else {
            $this->logger->alert("Consume event failed, messageId already exists: {$analyticData->getMessageId()}");
        }
        try {
            $this->consumedQueuedEventRepository->save($consumedEventQueueRecord);
        } catch (\Exception $e) {
            $this->logger->alert('add event record failed: ' . $e->getTraceAsString());
            throw $e;
        }
    }
}
