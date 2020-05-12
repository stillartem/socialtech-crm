<?php

namespace App\Domain\AsyncWorkers\Repository;

use App\Domain\AsyncWorkers\Entity\TaskEntityInterface;
use App\Domain\AsyncWorkers\ValueObject\TaskStatus;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use App\Domain\AsyncWorkers\Exception\PostponedException;

abstract class DoctrineTaskRepositoryAbstract extends EntityRepository
    implements TaskRepositoryInterface
{

    /**
     * @param TaskEntityInterface $task
     * @param string $workerId
     *
     * @return bool
     */
    public function lock(TaskEntityInterface $task, $workerId): bool
    {
        $qb = $this->_em->createQueryBuilder();
        $q = $qb->update($task->getClass(), 'w')
            ->set('w.workerId', $qb->expr()->literal($workerId))
            ->set('w.status', $qb->expr()->literal(TaskStatus::INPROGRESS))
            ->where('w.id = :taskid')
            ->andWhere("(w.workerId IS NULL OR w.workerId= :empty_value OR w.workerId='0')")
            ->setParameter('taskid', $task->getId())
            ->setParameter('empty_value', '')
            ->getQuery();

        $ret = (bool)$q->execute();
        if ($ret) {
            $task
                ->setStatus(TaskStatus::INPROGRESS)
                ->setWorkerId($workerId);
            $this->_em->refresh($task);
        }

        return $ret;
    }


    /**
     * @param TaskEntityInterface $item
     *
     */
    public function save(TaskEntityInterface $item): void
    {
        $this->_em->persist($item);
        $this->_em->flush();
    }


    /**
     * @param TaskEntityInterface $item
     */
    public function remove(TaskEntityInterface $item): void
    {
        $this->_em->remove($item);
        $this->_em->flush();
    }

    /**
     * @return QueryBuilder
     */
    public function removeAll(): QueryBuilder
    {
        return $this->createQueryBuilder('e')
            ->delete()
            ->getQuery()
            ->execute();
    }

    /**
     * @return void
     */
    public function clearMemory(): void
    {
        $this->_em->clear();
    }

    /**
     * @param int $limit
     *
     * @return TaskEntityInterface[]
     */
    public function getTasks(int $limit = 100): array
    {
        $qb = $this->createQueryBuilder('w')
            ->where('(w.workerId IS NULL OR w.workerId= :empty_value)')
            ->andWhere('(w.nextExecTime <= :today OR w.nextExecTime IS NULL)')
            ->andWhere('(w.status = :status_free OR w.status = :status_postponed)')
            ->orderBy('w.timestamp', 'ASC')
            ->setParameter('today', new \DateTime('now'), \Doctrine\DBAL\Types\Type::DATETIME)
            ->setParameter('status_free', TaskStatus::FREE)
            ->setParameter('status_postponed', TaskStatus::POSTPONED)
            ->setParameter('empty_value', '');

        return $this->getCustomFilterForGetTasks($qb)
            ->getQuery()
            ->setMaxResults($limit)
            ->getResult();
    }


    /**
     * @param QueryBuilder $qb
     *
     * @return QueryBuilder
     */
    protected function getCustomFilterForGetTasks(QueryBuilder $qb): QueryBuilder
    {
        return $qb;
    }


    /**
     * @param TaskEntityInterface $task
     *
     * @param \Throwable $exception
     *
     * @return void
     */
    abstract public function markAsError(TaskEntityInterface $task, \Throwable $exception);


    /**
     * @param TaskEntityInterface $task
     *
     * @return void
     */
    abstract public function markAsDone(TaskEntityInterface $task);


    /**
     * @param TaskEntityInterface $task
     * @param PostponedException $exception
     *
     * @return void
     */
    abstract public function markAsPostponed(TaskEntityInterface $task, PostponedException $exception);

    /**
     * @param bool $statement
     * @param string $message
     *
     * @throws \LogicException
     */
    protected function ensure($statement, $message)
    {
        if (!$statement) {
            throw new \LogicException($message);
        }
    }
}
