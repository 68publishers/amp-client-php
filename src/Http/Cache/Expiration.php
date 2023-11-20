<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Http\Cache;

use DateTimeImmutable;
use function is_string;
use function time;

final class Expiration
{
    private int $value;

    public function __construct(int $value)
    {
        $this->value = $value;
    }

    /**
     * @param string|int $expiration
     */
    public static function create($expiration): self
    {
        $time = time();
        $expiration = is_string($expiration)
            ? (int) (new DateTimeImmutable('now'))->modify($expiration)->format('U')
            : $time + $expiration;

        if ($time === $expiration) {
            $expiration -= 1;
        }

        return new self(
            $expiration,
        );
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function isFresh(): bool
    {
        return !$this->isExpired();
    }

    public function isExpired(): bool
    {
        return time() > $this->value;
    }
}
