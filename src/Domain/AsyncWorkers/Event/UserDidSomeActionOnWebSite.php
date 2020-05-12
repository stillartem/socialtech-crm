<?php

namespace App\Domain\AsyncWorkers\Event;

use App\Domain\Analytic\ValueObject\AnalyticData;
use Symfony\Contracts\EventDispatcher\Event;

class UserDidSomeActionOnWebSite extends Event
{
    public const EVENT_NAME = 'UserDidSomeActionOnWebSite.v1';

    /** @var AnalyticData */
    private AnalyticData $analyticData;

    /** @var int */
    private int $id;

    /**
     * @param array $content
     *
     * @return UserDidSomeActionOnWebSite
     */
    public static function deserializeFromArray(array $content): self
    {
        $event = new self();
        $analyticData = AnalyticData::fromScalar(
            $content['userId'],
            $content['sourceLabel'],
            $content['id'],
            $content['createdAt']['date']
        );
        $event->analyticData = $analyticData;
        $event->id = $content['id'];

        return $event;
    }

    /**
     * @return string
     */
    public function asJson(): string
    {
        return json_encode($this->analyticData->asArray());
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }
}
