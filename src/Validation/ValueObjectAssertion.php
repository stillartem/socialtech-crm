<?php

namespace App\Validation;

use App\Domain\Core\Exception\BadDataException;

final class ValueObjectAssertion
{
    /**
     * @param mixed $statement
     * @param string $field
     */
    public static function ensure($statement, string $field): void
    {
        if (!$statement) {
            throw BadDataException::forField($field);
        }
    }
}
