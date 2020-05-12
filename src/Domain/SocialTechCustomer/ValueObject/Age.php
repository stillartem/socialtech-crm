<?php

namespace App\Domain\SocialTechCustomer\ValueObject;

use App\Domain\Core\ValueObject\ValueObject;
use App\Validation\ValueObjectAssertion;

class Age implements ValueObject
{
    /** @var int */
    private int $value;

    private function __construct(int $value)
    {
        $this->value = $value;
    }

    /**
     * @param int $value
     *
     * @return static
     */
    public static function fromScalar(int $value): self
    {
        ValueObjectAssertion::ensure($value > 17, 'age');

        return new self($value);
    }

    public function __toString()
    {
        return (string)$this->value;
    }
}
