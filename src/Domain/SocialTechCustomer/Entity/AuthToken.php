<?php

namespace App\Domain\SocialTechCustomer\Entity;

use App\Library\Uuid\Uuid;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="auth_token")
 */
class AuthToken
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    private int $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private string $token;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private string $userId;


    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private string $nickName;


    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private \DateTime $expireAt;

    /**
     * @return \DateTime
     */
    public function getExpireAt(): \DateTime
    {
        return $this->expireAt;
    }

    public function __construct()
    {
        $this->token = Uuid::create();
    }

    /**
     * @param \DateTime $expireAt
     *
     * @return AuthToken
     */
    public function setExpireAt(\DateTime $expireAt): self
    {
        $this->expireAt = $expireAt;

        return $this;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getNickName(): string
    {
        return $this->nickName;
    }

    /**
     * @param string $nickName
     *
     * @return AuthToken
     */
    public function setNickName(string $nickName): AuthToken
    {
        $this->nickName = $nickName;

        return $this;
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @return string
     */
    public function getUserId(): string
    {
        return $this->userId;
    }

    /**
     * @param string $userId
     *
     * @return AuthToken
     */
    public function setUserId(string $userId): AuthToken
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * @return array
     */
    public function asArray(): array
    {
        return [
            'token' => $this->getToken(),
            'expireAt' => $this->getExpireAt(),
        ];
    }
}
