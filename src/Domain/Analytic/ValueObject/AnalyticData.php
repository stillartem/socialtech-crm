<?php

namespace App\Domain\Analytic\ValueObject;

use App\Domain\Core\ValueObject\Uuid4;
use App\Domain\Core\ValueObject\ValueObject;
use App\Validation\ValueObjectAssertion;

class AnalyticData implements ValueObject
{
    private int $messageId;

    /** @var Uuid4 */
    private Uuid4 $userId;

    /** @var string */
    private string $sourceLabel;

    /** @var \DateTime */
    private \DateTime $createdAt;

    private function __construct(Uuid4 $userId, string $sourceLabel, $id, \DateTime $createdAt)
    {
        $this->userId = $userId;
        $this->sourceLabel = $sourceLabel;
        $this->createdAt = $createdAt;
        $this->messageId = $id;
    }

    /**
     * @param string $userId
     * @param string $sourceLabel
     * @param int $id
     * @param string $date
     *
     * @return AnalyticData
     * @throws \Exception
     */
    public static function fromScalar(?string $userId, ?string $sourceLabel, ?int $id, ?string $date): AnalyticData
    {
        ValueObjectAssertion::ensure($userId !== null, 'userId');
        ValueObjectAssertion::ensure($sourceLabel !== null, 'sourceLabel');
        ValueObjectAssertion::ensure($id !== null, 'id');
        ValueObjectAssertion::ensure($date !== null, 'createdAt');

        $uuid = Uuid4::fromString($userId);
        $createdAt = new \DateTime($date);

        return new self($uuid, $sourceLabel, $id, $createdAt);
    }

    /**
     * @return Uuid4
     */
    public function getUserId(): Uuid4
    {
        return $this->userId;
    }

    /**
     * @return string
     */
    public function getSourceLabel(): string
    {
        return $this->sourceLabel;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * @return int
     */
    public function getMessageId(): int
    {
        return $this->messageId;
    }

    /**
     * @return array
     */
    public function asArray(): array
    {
        return [
            'id' => $this->getMessageId(),
            'sourceLabel' => $this->getSourceLabel(),
            'createdAt' => $this->getCreatedAt(),
            'userId' => (string)$this->getUserId(),
        ];
    }
}
