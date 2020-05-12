<?php

namespace App\Tests\Helper\RepositoryMock;

use App\Domain\SocialTechCustomer\Repository\AuthTokenRepository as BaseRepo;

class AuthTokenRepository extends BaseRepo
{
    public function validateToken(string $token): bool
    {
        if ($token === 'existing_token') {
            return true;
        }

        return false;
    }
}
