<?php

namespace App\Domain\SocialTechCustomer\Exception;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class AuthTokenIsWrongException extends AccessDeniedHttpException
{
    public const WRONG_AUTH_TOKEN = 'auth.auth_token_is_wrong';

    /**
     * @return AuthTokenIsWrongException
     */
    public static function WrongTokenException(): AuthTokenIsWrongException
    {
        return new self(self::WRONG_AUTH_TOKEN, null, 401, []);
    }
}
