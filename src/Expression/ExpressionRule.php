<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Expression;

use Closure;

final class ExpressionRule
{
    public string $value;

    /** @var Closure(int $index): bool */
    private Closure $matcher;

    /**
     * @param Closure(int $index): bool $matcher
     */
    private function __construct(
        string $value,
        Closure $matcher
    ) {
        $this->value = $value;
        $this->matcher = $matcher;
    }

    public static function eq(int $eq, string $value): self
    {
        return new self(
            $value,
            function (int $index) use ($eq): bool {
                return $index === $eq;
            },
        );
    }

    public static function range(int $from, int $to, string $value): self
    {
        return new self(
            $value,
            function (int $index) use ($from, $to): bool {
                return $index >= $from && $index <= $to;
            },
        );
    }

    public static function lt(int $lt, string $value): self
    {
        return new self(
            $value,
            function (int $index) use ($lt): bool {
                return $index < $lt;
            },
        );
    }

    public static function lte(int $lte, string $value): self
    {
        return new self(
            $value,
            function (int $index) use ($lte): bool {
                return $index <= $lte;
            },
        );
    }

    public static function gt(int $gt, string $value): self
    {
        return new self(
            $value,
            function (int $index) use ($gt): bool {
                return $index > $gt;
            },
        );
    }

    public static function gte(int $gte, string $value): self
    {
        return new self(
            $value,
            function (int $index) use ($gte): bool {
                return $index >= $gte;
            },
        );
    }

    public static function positive(string $value): self
    {
        return new self(
            $value,
            function (): bool {
                return true;
            },
        );
    }

    public function matches(int $index): bool
    {
        return ($this->matcher)($index);
    }
}
