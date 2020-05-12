<?php

namespace App\Domain\AsyncWorkers\Repository;


use App\Domain\AsyncWorkers\Entity\TaskEntityInterface;
use App\Domain\AsyncWorkers\Exception\PostponedException;

interface TaskRepositoryInterface
{
    /**
     * @param TaskEntityInterface $task
     *
     * @param string $workerId
     *
     * @return bool
     */
    public function lock(TaskEntityInterface $task, $workerId);


    /**
     * @param TaskEntityInterface $task
     *
     * @return void
     */
    public function markAsDone(TaskEntityInterface $task);


    /**
     * @param TaskEntityInterface $task
     *
     * @param \Throwable $exception
     *
     * @return void
     */
    public function markAsError(TaskEntityInterface $task, \Throwable $exception);


    /**
     * @param TaskEntityInterface $task
     *
     * @param PostponedException $exception
     *
     * @return void
     */
    public function markAsPostponed(TaskEntityInterface $task, PostponedException $exception);


    /**
     * @param TaskEntityInterface $task
     *
     * @return void
     */
    public function save(TaskEntityInterface $task);


    /**
     * @param int $limit
     *
     * @return TaskEntityInterface[]
     */
    public function getTasks(int $limit = 100);


    /**
     * @return void
     */
    public function clearMemory();
}
