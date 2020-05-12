<?php

namespace App\Domain\RabbitMQ\Entity;

use App\Domain\AsyncWorkers\ValueObject\TaskStatus;
use Doctrine\ORM\Mapping as ORM;

abstract class AbstractQueuedEvent extends TaskEntityAbstract
{

    /**
     * @var int
     *
     * @ORM\Column(type="integer", name="uid")
     */
    protected int $messageId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetimetz")
     */
    protected \DateTime $timestamp;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $routingKey;

    /**
     * @var array
     *
     * @ORM\Column(type="json", name="event_body", nullable=true)
     */
    protected $eventContent;

    /**
     * @var TaskStatus
     * @ORM\Column(type="string", name="w_status", nullable=true)
     */
    protected $status;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", name="w_next_exec_time", nullable=true)
     */
    protected $nextExecTime;

    /**
     * @var string
     * @ORM\Column(type="text", name="w_last_error", nullable=true)
     */
    protected $lastError;

    /**
     * @var string
     * @ORM\Column(type="string", name="w_worker_id", nullable=true)
     */
    protected $workerId;


    /**
     * @return int
     */
    public function getMessageId()
    {
        return $this->messageId;
    }


    /**
     * @return \DateTime
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }


    /**
     * @return string
     */
    public function getEventName(): ?string
    {
        return $this->routingKey;
    }


    /**
     * @return array
     */
    public function getEventContent(): ?array
    {
        return $this->eventContent;
    }


    /**
     * @return \DateTime|null
     */
    public function getNextExecTime(): ?\DateTime
    {
        return $this->nextExecTime;
    }


    /**
     * @param int $messageId
     *
     * @return AbstractQueuedEvent
     */
    public function setMessageId(int $messageId): AbstractQueuedEvent
    {
        $this->messageId = $messageId;

        return $this;
    }


    /**
     * @param \DateTime $timestamp
     *
     * @return AbstractQueuedEvent
     */
    public function setTimestamp(\DateTime $timestamp): AbstractQueuedEvent
    {
        $this->timestamp = $timestamp;

        return $this;
    }


    /**
     * @param string $routingKey
     *
     * @return AbstractQueuedEvent
     */
    public function setRoutingKey(string $routingKey): AbstractQueuedEvent
    {
        $this->routingKey = $routingKey;

        return $this;
    }


    /**
     * @param array $eventContent
     *
     * @return AbstractQueuedEvent
     */
    public function setEventContent(array $eventContent): AbstractQueuedEvent
    {
        $this->eventContent = $eventContent;

        return $this;
    }
}
