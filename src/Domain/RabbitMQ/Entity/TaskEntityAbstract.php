<?php

namespace App\Domain\RabbitMQ\Entity;

use App\Domain\AsyncWorkers\Entity\TaskEntityInterface;
use App\Domain\AsyncWorkers\ValueObject\TaskStatus;

abstract class TaskEntityAbstract implements TaskEntityInterface
{
    /** @var int */
    protected $id;

    /** @var int */
    protected $workerId;

    /** @var TaskStatus */
    protected $status;

    /** @var \DateTime */
    protected $nextExecTime;

    /** @var string */
    protected $lastError;


    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }


    /**
     * @param int $workerId
     *
     * @return TaskEntityAbstract
     */
    public function setWorkerId($workerId): self
    {
        $this->workerId = $workerId;

        return $this;
    }


    /**
     * @return string
     */
    public function getWorkerId(): ?string
    {
        return $this->workerId;
    }


    /**
     * @param string $status
     *
     * @return TaskEntityAbstract
     */
    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }


    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }


    /**
     * @param string $lastError
     *
     * @return TaskEntityAbstract
     */
    public function setLastError($lastError): self
    {
        $this->lastError = $lastError;

        return $this;
    }


    /**
     * @return string
     */
    public function getLastError(): ?string
    {
        return $this->lastError;
    }


    /**
     * @param \DateTime $nextExecTime
     *
     * @return TaskEntityAbstract
     */
    public function setNextExecTime($nextExecTime): self
    {
        $this->nextExecTime = $nextExecTime;

        return $this;
    }


    /**
     * @return \DateTime
     */
    public function getNextExecTime(): ?\DateTime
    {
        return $this->nextExecTime;
    }


    /**
     * @return string
     */
    public function getClass(): string
    {
        return static::class;
    }

    /**`
     * @param int $id
     *
     * @return TaskEntityAbstract
     */
    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }
}
