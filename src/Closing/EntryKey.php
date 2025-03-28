<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Closing;

final class EntryKey
{
    private string $value;

    private function __construct(
        string $value
    ) {
        $this->value = $value;
    }

    public static function position(string $positionCode): self
    {
        return new self(
            'p:' . $positionCode,
        );
    }

    public static function banner(string $positionCode, string $bannerId): self
    {
        return new self(
            'b:' . $positionCode . ':' . $bannerId,
        );
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
