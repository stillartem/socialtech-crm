<?php

namespace App\Domain\RabbitMQ\Repository;

use App\Domain\AsyncWorkers\Entity\TaskEntityInterface;
use App\Domain\AsyncWorkers\Exception\PostponedException;
use App\Domain\AsyncWorkers\Repository\DoctrineTaskRepositoryAbstract;
use App\Domain\RabbitMQ\Entity\ConsumedQueuedEvent;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;

class ConsumedQueuedEventRepository extends DoctrineTaskRepositoryAbstract
{
    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        /** @var EntityManagerInterface $manager */
        $manager = $registry->getManagerForClass(ConsumedQueuedEvent::class);
        if ($manager === null) {
            throw new \LogicException(
                sprintf(
                    'Could not find the entity manager for class "%s". Check your Doctrine configuration to make sure it is configured to load this entity?s metadata.',
                    ConsumedQueuedEvent::class
                )
            );
        }

        parent::__construct($manager, $manager->getClassMetadata(ConsumedQueuedEvent::class));
    }


    /**
     * @param TaskEntityInterface $task
     *
     * @return void
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function markAsDone(TaskEntityInterface $task): void
    {
        /** @var ConsumedQueuedEvent $task */
        if (!($task instanceof ConsumedQueuedEvent)) {
            throw new \RuntimeException(
                'Invalid worker Entity. Should be ' . ConsumedQueuedEvent::class . ', got ' . get_class($task)
            );
        }

        $archived = $task->getArchived();

        $this->_em->persist($archived);
        $this->_em->remove($task);
        $this->_em->flush();
    }

    public function markAsError(TaskEntityInterface $task, \Throwable $exception)
    {
        // TODO: Implement markAsError() method.
    }

    public function markAsPostponed(TaskEntityInterface $task, PostponedException $exception)
    {
        // TODO: Implement markAsPostponed() method.
    }
}
