<?php

namespace App\Domain\Analytic\Event;

use App\Domain\Analytic\ValueObject\AnalyticData;
use Symfony\Contracts\EventDispatcher\Event;

class TrackAnalytic extends Event
{
    /** @var AnalyticData */
    private AnalyticData $analyticData;

    private function __construct(AnalyticData $analyticData)
    {
        $this->analyticData = $analyticData;
    }

    /**
     * @param string|null $userId
     * @param string|null $sourceLabel
     * @param int|null $id
     * @param string|null $date
     *
     * @return TrackAnalytic
     * @throws \Exception
     */
    public static function fromScalar(?string $userId, ?string $sourceLabel, ?int $id, ?string $date): TrackAnalytic
    {
        $analyticData = AnalyticData::fromScalar($userId, $sourceLabel, $id, $date);

        return new self($analyticData);
    }

    /**
     * @return AnalyticData
     */
    public function getAnalyticData(): AnalyticData
    {
        return $this->analyticData;
    }
}
