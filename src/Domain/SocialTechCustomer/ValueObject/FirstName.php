<?php

namespace App\Domain\SocialTechCustomer\ValueObject;

use App\Domain\Core\ValueObject\ValueObject;
use App\Validation\ValueObjectAssertion;

class FirstName implements ValueObject
{
    /** @var string */
    private string $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    /**
     * @param string $value
     *
     * @return static
     */
    public static function fromScalar(string $value): self
    {
        ValueObjectAssertion::ensure(\strlen($value) > 2, 'firstName');

        return new self($value);
    }

    public function __toString()
    {
        return $this->value;
    }
}
