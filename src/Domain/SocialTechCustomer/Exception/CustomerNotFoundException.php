<?php

namespace App\Domain\SocialTechCustomer\Exception;

use App\Domain\Core\Exception\BusinessExceptionInterface;

class CustomerNotFoundException extends \Exception implements BusinessExceptionInterface
{
    public static function forNickName(string $nickName): CustomerNotFoundException
    {
        return new self('Customer not found for nickname. ' . $nickName);
    }
}
