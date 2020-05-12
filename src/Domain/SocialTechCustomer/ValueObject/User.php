<?php

namespace App\Domain\SocialTechCustomer\ValueObject;

use App\Domain\Core\ValueObject\Uuid4;
use App\Domain\Core\ValueObject\ValueObject;

class User implements ValueObject
{
    /** @var FirstName */
    private FirstName $firstName;

    /** @var LastName */
    private LastName $lastName;

    /** @var NickName */
    private NickName $nickName;

    /** @var Age */
    private Age $age;

    /** @var HashedPassword */
    private HashedPassword $hashedPassword;

    /** @var Uuid4 */
    private Uuid4 $uuid;

    public function __construct(
        FirstName $firstName,
        LastName $lastName,
        NickName $nickName,
        Age $age,
        HashedPassword $hashedPassword,
        Uuid4 $uuid
    ) {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->nickName = $nickName;
        $this->age = $age;
        $this->hashedPassword = $hashedPassword;
        $this->uuid = $uuid;
    }

    /**
     * @return string
     */
    public function asJson(): string
    {
        return json_encode($this->asArray());
    }

    public function asArray(): array
    {
        return [
            'uuid' => (string)$this->uuid,
            'firstName' => (string)$this->firstName,
            'lastName' => (string)$this->lastName,
            'nickName' => (string)$this->nickName,
            'age' => (string)$this->age,
            'hashedPassword' => (string)$this->hashedPassword,
        ];
    }

    /**
     * @return Uuid4
     */
    public function getUuid(): Uuid4
    {
        return $this->uuid;
    }

    /**
     * @return FirstName
     */
    public function getFirstName(): FirstName
    {
        return $this->firstName;
    }

    /**
     * @return LastName
     */
    public function getLastName(): LastName
    {
        return $this->lastName;
    }

    /**
     * @return NickName
     */
    public function getNickName(): NickName
    {
        return $this->nickName;
    }

    /**
     * @return Age
     */
    public function getAge(): Age
    {
        return $this->age;
    }

    /**
     * @return HashedPassword
     */
    public function getHashedPassword(): HashedPassword
    {
        return $this->hashedPassword;
    }
}
