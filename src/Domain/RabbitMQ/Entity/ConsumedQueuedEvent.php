<?php

namespace App\Domain\RabbitMQ\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="event_consumed_queue")
 * @ORM\HasLifecycleCallbacks()
 */
class ConsumedQueuedEvent extends AbstractQueuedEvent
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @var ConsumedArchivedEvent
     */
    protected $archived;


    /**
     * @param ConsumedArchivedEvent $archived
     *
     * @return ConsumedQueuedEvent
     */
    public function setArchived(ConsumedArchivedEvent $archived): ConsumedQueuedEvent
    {
        $this->archived = $archived;

        return $this;
    }


    /**
     * @return ConsumedArchivedEvent|null
     */
    public function getArchived(): ?ConsumedArchivedEvent
    {
        return $this->archived;
    }
}
