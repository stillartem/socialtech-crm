<?php

namespace App\Library\Uuid;

final class Uuid
{
    public const VALID_PATTERN = '[0-9a-f]{8}-([0-9a-f]{4}-){3}[0-9a-f]{12}';

    /**
     * @var string
     */
    private $value;

    /**
     * @param string|null $uuid
     *
     * @throws \Exception
     */
    public function __construct(string $uuid = null)
    {
        if (!self::isValid($uuid)) {
            throw new \Exception("Provided UUID is not valid: " . $uuid);
        }

        $this->value = $uuid;
    }


    /**
     * @return self
     * @throws \Exception
     */
    public static function create()
    {
        return new self(self::uuid4());
    }

    /**
     * Returns a version 4 UUID
     *
     * @return string
     */
    public static function uuid4()
    {
        $bytes =
            function_exists('openssl_random_pseudo_bytes')
                ? openssl_random_pseudo_bytes(16)
                : self::generateBytes(16);

        $hash = bin2hex($bytes);

        // Set the version number
        $timeHi = hexdec(substr($hash, 12, 4)) & 0x0fff;
        $timeHi &= ~(0xf000);
        $timeHi |= 4 << 12;

        // Set the variant to RFC 4122
        $clockSeqHi = hexdec(substr($hash, 16, 2)) & 0x3f;
        $clockSeqHi &= ~(0xc0);
        $clockSeqHi |= 0x80;

        $fields = [
            'time_low'                  => substr($hash, 0, 8),
            'time_mid'                  => substr($hash, 8, 4),
            'time_hi_and_version'       => sprintf('%04x', $timeHi),
            'clock_seq_hi_and_reserved' => sprintf('%02x', $clockSeqHi),
            'clock_seq_low'             => substr($hash, 18, 2),
            'node'                      => substr($hash, 20, 12),
        ];

        return vsprintf(
            '%08s-%04s-%04s-%02s%02s-%012s',
            $fields
        );
    }


    /**
     * @param string $length
     *
     * @return string
     */
    private static function generateBytes($length)
    {
        $bytes = '';

        for ($i = 1; $i <= $length; $i++) {
            $bytes = chr(mt_rand(0, 256)) . $bytes;
        }

        return $bytes;
    }


    /**
     * @param string $uuid
     *
     * @return bool
     */
    public static function isValid($uuid): bool
    {
        return (bool)preg_match('/^' . self::VALID_PATTERN . '$/', $uuid);
    }


    /**
     * @return string
     */
    public function __toString(): string
    {
        return (string)$this->value;
    }
}