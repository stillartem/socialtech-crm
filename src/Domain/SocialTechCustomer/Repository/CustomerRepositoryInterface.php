<?php

namespace App\Domain\SocialTechCustomer\Repository;

use App\Domain\SocialTechCustomer\ValueObject\NickName;
use App\Domain\SocialTechCustomer\ValueObject\User;

interface CustomerRepositoryInterface
{
    /**
     * @param NickName $email
     *
     * @return User
     */
    public function getCustomerByNickName(NickName $email): User;

    /** @var User */
    public function saveCustomer(User $user): void;
}
