<?php

namespace App\Domain\Core\ValueObject;

use App\Library\Uuid\Uuid;
use App\Validation\ValueObjectAssertion;

class Uuid4
{
    /** @var string */
    protected $value;


    /**
     * @param string $value
     */
    protected function __construct($value)
    {
        ValueObjectAssertion::ensure((bool)preg_match('/^' . Uuid::VALID_PATTERN . '$/', $value), 'uuid');
        $this->value = $value;
    }


    /**
     * @param string $value
     *
     * @return self
     */
    public static function fromString($value): self
    {
        return new self($value);
    }


    /**
     * @return self
     * @throws \Exception
     */
    public static function generate(): self
    {
        return new self((string)Uuid::uuid4());
    }


    /**
     * @param Uuid4 $other
     *
     * @return bool
     */
    public function equals(Uuid4 $other): bool
    {
        return $this->value === $other->value;
    }


    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->value;
    }
}
