<?php

namespace App\Domain\Core\Exception;

interface ErrorCodeExceptionInterface
{
    /**
     * @return string
     */
    public function getErrorCode(): string;
}