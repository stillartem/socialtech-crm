<?php

namespace App\Domain\RabbitMQ\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="event_consumed_archive")
 * @ORM\HasLifecycleCallbacks()
 */
class ConsumedArchivedEvent extends AbstractQueuedEvent
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;
}
