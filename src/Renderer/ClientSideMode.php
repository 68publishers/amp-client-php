<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Renderer;

use InvalidArgumentException;
use function in_array;
use function sprintf;

final class ClientSideMode
{
    public const Modes = [
        self::Managed,
        self::Embed,
    ];

    private const Managed = 'managed';
    private const Embed = 'embed';

    private string $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function managed(): self
    {
        return new self(self::Managed);
    }

    public static function embed(): self
    {
        return new self(self::Embed);
    }

    public static function fromValue(string $value): self
    {
        if (!in_array($value, self::Modes, true)) {
            throw new InvalidArgumentException(sprintf(
                'Value "%s" is not valid client side mode.',
                $value,
            ));
        }

        return new self($value);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function isManaged(): bool
    {
        return self::Managed === $this->value;
    }

    public function isEmbed(): bool
    {
        return self::Embed === $this->value;
    }
}
