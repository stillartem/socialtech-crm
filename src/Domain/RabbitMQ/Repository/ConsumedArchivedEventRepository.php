<?php

namespace App\Domain\RabbitMQ\Repository;

use App\Domain\RabbitMQ\Entity\ConsumedQueuedEvent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class ConsumedArchivedEventRepository extends ServiceEntityRepository
{
    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ConsumedQueuedEvent::class);
    }

}
