<?php

namespace App\Domain\AsyncWorkers\ValueObject;

class TaskStatus
{
    /* @var string * */
    public const FREE = 'Free';

    /* @var string * */
    public const INPROGRESS = 'In Progress';

    /* @var string * */
    public const DONE = 'Done';

    /* @var string * */
    public const ERROR = 'Error';

    /* @var string * */
    public const POSTPONED = 'Postponed';

    /* @var string * */
    private string $status;


    /**
     * @param string $status
     *
     * @throws \Exception
     */
    public function __construct($status)
    {
        if (!in_array($status, [self::FREE, self::DONE, self::ERROR, self::INPROGRESS])) {
            throw new \Exception('Invalid task status: ' . $status);
        }
        $this->status = $status;
    }


    /**
     * @return string
     */
    public function getValue()
    {
        return $this->status;
    }


    /**
     * @return string
     */
    public function __toString()
    {
        return $this->status;
    }
}
