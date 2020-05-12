<?php

namespace App\Tests\Port\Worker;

use App\Domain\AsyncWorkers\Event\UserDidSomeActionOnWebSite;
use App\Domain\Core\ValueObject\Uuid4;
use App\Domain\RabbitMQ\Entity\ConsumedQueuedEvent;
use App\Domain\RabbitMQ\Repository\ConsumedQueuedEventRepository;
use App\Port\Worker\ConsumedEventQueueProcessor;
use App\Tests\BaseWebTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ConsumedEventQueueProcessorTest extends BaseWebTestCase
{
    /** @var ConsumedQueuedEventRepository | MockObject */
    private $repository;

    /** @var EventDispatcherInterface | MockObject */
    private $eventDispatcher;

    private ConsumedEventQueueProcessor $processor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository =
            $this->getMockBuilder(ConsumedQueuedEventRepository::class)->disableOriginalConstructor()->getMock();
        $this->eventDispatcher =
            $this->getMockBuilder(EventDispatcherInterface::class)->disableOriginalConstructor()->getMock();
        $this->processor = new ConsumedEventQueueProcessor($this->repository, $this->eventDispatcher);
        $this->processor->setItemsLimit(10)
            ->setMemoryLimit(132894640)
            ->setTimeout(1)
            ->setCycles(1)
            ->setExecutionTimeLimit('1 day');
    }

    public function test_it_should_dispatch_queue_event()
    {
        $task = (new ConsumedQueuedEvent())
            ->setId(123)
            ->setMessageId(1234)
            ->setTimestamp(new \DateTime())
            ->setRoutingKey(UserDidSomeActionOnWebSite::EVENT_NAME)
            ->setEventContent(
                [
                    'userId' => (string)Uuid4::generate(),
                    'sourceLabel' => 'testEnv',
                    'createdAt' => [
                        'date' => (new \DateTime())->format('d-m-Y'),
                    ],
                    'id' => 1,

                ]
            );

        $this->repository->expects($this->atLeastOnce())->method('getTasks')->willReturn([$task]);
        $this->repository->expects($this->atLeastOnce())->method('lock')->willReturn(true);

        $ou = $this->getMockBuilder(SymfonyStyle::class)->disableOriginalConstructor()->getMock();

        $event = UserDidSomeActionOnWebSite::deserializeFromArray($task->getEventContent());

        $this->eventDispatcher->expects($this->once())->method('dispatch')->with($event);

        $this->processor->execute($ou);
    }
}
