<?php

namespace App\Domain\SocialTechCustomer\ValueObject;

use App\Domain\Core\ValueObject\ValueObject;
use App\Validation\ValueObjectAssertion;

final class HashedPassword implements ValueObject
{
    /** @var string */
    private string $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->value;
    }

    /**
     * @param HashedPassword $other
     *
     * @return bool
     */
    public function equals(HashedPassword $other)
    {
        return $this->value === $other->value;
    }

    /**
     * @param $plainPassword
     * @param int $algorithm
     *
     * @return HashedPassword
     */
    public static function generate($plainPassword, $algorithm = PASSWORD_DEFAULT): HashedPassword
    {
        ValueObjectAssertion::ensure(strlen($plainPassword) > 3, 'password');

        return new static((string)password_hash($plainPassword, $algorithm));
    }

    /**
     * @param $hash
     *
     * @return HashedPassword
     */
    public static function generateFromHash($hash): HashedPassword
    {
        return new static($hash);
    }

    /**
     * @param $plainPassword
     *
     * @return bool
     */
    public function verify($plainPassword): bool
    {
        return password_verify($plainPassword, $this->value);
    }
}
