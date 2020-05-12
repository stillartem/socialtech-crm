<?php

namespace App\Port\Worker;

use App\Domain\AsyncWorkers\Entity\TaskEntityInterface;
use App\Domain\AsyncWorkers\Event\UserDidSomeActionOnWebSite;
use App\Domain\AsyncWorkers\ValueObject\TaskStatus;
use App\Domain\RabbitMQ\Entity\ConsumedArchivedEvent;
use App\Domain\RabbitMQ\Entity\ConsumedQueuedEvent;
use App\Domain\RabbitMQ\Repository\ConsumedQueuedEventRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\EventDispatcher\Event;

class ConsumedEventQueueProcessor extends AsyncWorkerAbstract
{
    /** @var EventDispatcherInterface */
    private EventDispatcherInterface $dispatcher;

    /**
     * @param ConsumedQueuedEventRepository $consumedQueuedEventRepository
     * @param EventDispatcherInterface      $dispatcher
     */
    public function __construct(
        ConsumedQueuedEventRepository $consumedQueuedEventRepository,
        EventDispatcherInterface $dispatcher
    ) {
        $this->dispatcher = $dispatcher;

        parent::__construct($consumedQueuedEventRepository);
    }

    /**
     * @param TaskEntityInterface $task
     *
     * @return void
     * @throws \Exception
     */
    protected function handle(TaskEntityInterface $task): void
    {
        /** @var ConsumedQueuedEvent $task */
        if (!($task instanceof ConsumedQueuedEvent)) {
            throw new \RuntimeException(
                'Invalid worker Entity. Should be ' . ConsumedQueuedEvent::class . ', got ' . \get_class($task)
            );
        }

        $this->dispatcher->dispatch($this->getEventByTask($task));

        /** @var ConsumedArchivedEvent $archived */
        $archived = (new ConsumedArchivedEvent())
            ->setMessageId($task->getMessageId())
            ->setTimestamp($task->getTimestamp())
            ->setRoutingKey($task->getEventName())
            ->setEventContent($task->getEventContent())
            ->setStatus(new TaskStatus(TaskStatus::DONE))
            ->setLastError($task->getLastError())
            ->setWorkerId($task->getWorkerId());
        $task->setArchived($archived);
    }


    /**
     * @param ConsumedQueuedEvent $task
     *
     * @return Event
     * @throws \Exception
     */
    private function getEventByTask(ConsumedQueuedEvent $task): Event
    {
        switch ($task->getEventName()) {
            case UserDidSomeActionOnWebSite::EVENT_NAME:
                $event = UserDidSomeActionOnWebSite::deserializeFromArray($task->getEventContent());
                break;
            default:
                throw new \Exception('Event routing key is unknown '. $task->getEventName());
        }

        return $event;
    }
}
