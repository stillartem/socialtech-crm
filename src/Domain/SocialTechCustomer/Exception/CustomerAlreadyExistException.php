<?php

namespace App\Domain\SocialTechCustomer\Exception;

use App\Domain\Core\Exception\BusinessExceptionInterface;
use App\Domain\Core\ValueObject\Uuid4;
use App\Domain\SocialTechCustomer\ValueObject\NickName;

class CustomerAlreadyExistException extends \Exception implements BusinessExceptionInterface
{
    public static function withNickName(NickName $nickName): CustomerAlreadyExistException
    {
        return new self('Customer with nickname ' . $nickName . ' already exist');
    }

    public function getErrorCode()
    {
        return 'customer.already.exist';
    }
}
