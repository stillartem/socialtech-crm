<?php

namespace App\Tests\Helper\RepositoryMock;

use App\Domain\Core\ValueObject\Uuid4;
use App\Domain\SocialTechCustomer\Exception\CustomerNotFoundException;
use App\Domain\SocialTechCustomer\Repository\CustomerRepository as BaseRepository;
use App\Domain\SocialTechCustomer\ValueObject\Age;
use App\Domain\SocialTechCustomer\ValueObject\FirstName;
use App\Domain\SocialTechCustomer\ValueObject\HashedPassword;
use App\Domain\SocialTechCustomer\ValueObject\LastName;
use App\Domain\SocialTechCustomer\ValueObject\NickName;
use App\Domain\SocialTechCustomer\ValueObject\User;

class CustomerRepository extends BaseRepository
{
    public function getCustomerByNickName(NickName $nickname): User
    {
        if ((string)$nickname === 'existingUser') {
            return self::getTestUser();
        }
        throw CustomerNotFoundException::forNickName((string)$nickname);

    }

    /**
     * @return User
     * @throws \Exception
     */
    public static function getTestUser(): User
    {
        return new User(
            FirstName::fromScalar('test'),
            LastName::fromScalar('testovich'),
            NickName::fromScalar('existingUser'),
            Age::fromScalar(18),
            HashedPassword::generate('test'),
            Uuid4::fromString(Uuid4::generate()));
    }
}
