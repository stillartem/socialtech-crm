<?php

namespace App\Port\Worker;

use App\Domain\AsyncWorkers\Entity\TaskEntityInterface;
use App\Domain\AsyncWorkers\Exception\MaxAllowedMemoryException;
use App\Domain\AsyncWorkers\Exception\MaxExecutionTimeException;
use App\Domain\AsyncWorkers\Exception\PostponedException;
use App\Domain\AsyncWorkers\Repository\TaskRepositoryInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

abstract class AsyncWorkerAbstract
{
    public const MICROSECONDS_IN_ONE_SECONDS = 1000000;

    /** @var string */
    protected string $workerId;

    /** @var int */
    private int $itemsLimit = 100;

    /** @var int */
    private int $memoryLimit;

    /** @var int */
    private int $cycles;

    /** @var int */
    private int $timeout;

    /** @var string */
    private string $executionTimeLimit;

    /** @var bool */
    private bool $stopWorker = false;

    /** @var SymfonyStyle */
    private SymfonyStyle $output;

    /** @var TaskRepositoryInterface */
    private TaskRepositoryInterface $repository;


    public function __construct(TaskRepositoryInterface $repository)
    {
        $this->repository = $repository;
        $this->workerId = $this->generateWorkerId();
    }


    /**
     * @param int $itemsLimit
     *
     * @return self
     */
    public function setItemsLimit($itemsLimit): self
    {
        $this->itemsLimit = (int)$itemsLimit;

        return $this;
    }


    /**
     * @return int
     */
    public function getItemsLimit(): int
    {
        return $this->itemsLimit;
    }


    /**
     * @param int $memoryLimit
     *
     * @return self
     */
    public function setMemoryLimit($memoryLimit): self
    {
        $this->memoryLimit = $memoryLimit;

        return $this;
    }


    /**
     * @return int
     */
    public function getMemoryLimit(): int
    {
        return $this->memoryLimit;
    }


    /**
     * @param int $cycles
     *
     * @return self
     */
    public function setCycles($cycles): self
    {
        $this->cycles = $cycles;

        return $this;
    }


    /**
     * @return int
     */
    public function getCycles(): int
    {
        return $this->cycles;
    }


    /**
     * @param SymfonyStyle $out
     *
     * throws \Exception
     */
    final public function execute(SymfonyStyle $out): void
    {
        $this->output = $out;
        $iterations = 0;
        $timeStart = new \DateTimeImmutable();

        while ($this->getCycles() > $iterations) {
            $this->debug("starting iteration #" . $iterations);
            $this->run();
            $iterations++;

            if ($out->isVerbose() && !$out->isDebug()) {
                $out->progressAdvance(1);
            }

            if ($this->stopWorker) {
                die("Worker {$this->workerId} has been stopped successfully \n");
            }

            $memoryUsed = memory_get_usage();
            if ($memoryUsed >= $this->getMemoryLimit()) {
                throw MaxAllowedMemoryException::forLimits($this->getMemoryLimit(), $memoryUsed);
            }

            if (new \DateTime() > $timeStart->modify('+ ' . $this->getExecutionTimeLimit())) {
                throw MaxExecutionTimeException::forSeconds($this->getExecutionTimeLimit());
            }

            $this->debug('sleep');
            usleep(self::MICROSECONDS_IN_ONE_SECONDS * $this->getTimeout());
        }
    }


    /**
     * @param TaskEntityInterface $entity
     *
     * @return int
     */
    protected function lock(TaskEntityInterface $entity): int
    {
        return $this->getRepository()->lock($entity, $this->getWorkerId());
    }


    /**
     * @param TaskEntityInterface $entity
     */
    protected function markAsDone(TaskEntityInterface $entity): void
    {
        $this->getRepository()->markAsDone($entity);
    }


    /**
     * @param TaskEntityInterface $entity
     * @param \Throwable $exception
     */
    protected function markAsError(TaskEntityInterface $entity, \Throwable $exception): void
    {
        $this->getRepository()->markAsError($entity, $exception);
    }


    /**
     * @param TaskEntityInterface $entity
     * @param PostponedException $exception
     */
    protected function markAsPostponed(TaskEntityInterface $entity, PostponedException $exception): void
    {
        $this->getRepository()->markAsPostponed($entity, $exception);
    }


    /**
     * Execute processing items from queue
     *
     * @return  void
     */
    public function run(): void
    {
        $repo = $this->getRepository();
        $repo->clearMemory();

        $tasks = $repo->getTasks(
            $this->getItemsLimit()
        );
        $this->debug("taken " . count($tasks) . " tasks");

        foreach ($tasks as $task) {
            if ($this->lock($task)) {
                $this->debug("task #" . $task->getId() . " locked");
                try {
                    $this->handle($task);
                    $this->markAsDone($task);
                    $this->debug("task #" . $task->getId() . " done");
                } catch (PostponedException $exception) {
                    $this->debug("task #" . $task->getId() . " postponed");
                    $this->markAsPostponed($task, $exception);
                } catch (\Throwable $exception) {
                    $this->debug("task #" . $task->getId() . " failed: " . $exception->getMessage());
                    $this->logException($exception);
                    $this->markAsError($task, $exception);
                }
            } else {
                $this->debug("failed to lock task #" . $task->getId());
            }
        }
    }


    /**
     * @param int $timeout
     *
     * @return self
     */
    public function setTimeout($timeout): self
    {
        $this->timeout = $timeout;

        return $this;
    }


    /**
     * @return int
     */
    public function getTimeout(): int
    {
        return $this->timeout;
    }


    /**
     * @param string $executionTimeLimit
     *
     * @return self
     */
    public function setExecutionTimeLimit($executionTimeLimit): self
    {
        $this->executionTimeLimit = $executionTimeLimit;

        return $this;
    }


    /**
     * @return string
     */
    public function getExecutionTimeLimit(): string
    {
        return $this->executionTimeLimit;
    }


    /**
     * @return string
     */
    protected function generateWorkerId(): string
    {
        return gethostname() . '_'
            . substr(strrchr(static::class, "\\"), 1) . '_'
            . uniqid('', false)
            . '_p-' . getmypid();
    }


    /**
     * @return void
     */
    public function stopWorker(): void
    {
        echo "Start stopping worker {$this->workerId} \n";
        $this->stopWorker = true;

    }


    /**
     * @return string
     */
    protected function getWorkerId(): string
    {
        return $this->workerId;
    }


    /**
     * @param TaskEntityInterface $entity
     *
     * @return mixed
     * @throws \Exception
     * @throws PostponedException
     */
    abstract protected function handle(TaskEntityInterface $entity);


    /**
     * @return TaskRepositoryInterface
     */
    protected function getRepository(): TaskRepositoryInterface
    {
        return $this->repository;
    }

    /**
     * @param \Throwable $exception
     */
    protected function logException(\Throwable $exception): void
    {
        if (empty($this->output)) {
            return;
        }
        var_dump($exception->getMessage());die;

        $this->output->getErrorStyle()->error(
            [
                '[' . $this->getTimestamp() . ']',
                '"' . $exception->getMessage() . '"',
                $exception->getTraceAsString(),
            ]
        );
    }

    /**
     * @param string
     */
    protected function debug($message): void
    {
        if (empty($this->output)) {
            return;
        }

        static $start;

        $this->output->writeln(
            sprintf(
                "[%s] %s - %.4f sec",
                $this->getTimestamp(),
                $message,
                microtime(true) - $start
            ),
            SymfonyStyle::VERBOSITY_DEBUG
        );

        $start = microtime(true);
    }

    /**
     * @return string
     */
    protected function getTimestamp(): string
    {
        return (new \DateTime())->format('d.m.Y H:i:s.u');
    }
}
