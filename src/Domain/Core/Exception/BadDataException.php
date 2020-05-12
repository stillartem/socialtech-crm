<?php

namespace App\Domain\Core\Exception;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class BadDataException extends BadRequestHttpException
{
    /** @var string */
    private string $errorCode;

    public static function forField(string $fieldName): BadDataException
    {
        $exception = new self('Field ' . $fieldName . ' has wrong format');
        $exception->errorCode = 'field.' . $fieldName . '.invalid';

        return $exception;
    }

    /**
     * @return string
     */
    public function getErrorCode(): string
    {
        return $this->errorCode;
    }
}
