<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Response;

use SixtyEightPublishers\AmpClient\Response\ValueObject\Position;
use SixtyEightPublishers\AmpClient\Response\ValueObject\Settings;

final class BannersResponse
{
    private Settings $settings;

    /** @var array<string, Position> */
    private array $positions;

    /**
     * @param array<string, Position> $positions
     */
    public function __construct(
        Settings $settings,
        array $positions
    ) {
        $this->settings = $settings;
        $this->positions = $positions;
    }

    public function getSettings(): Settings
    {
        return $this->settings;
    }

    /**
     * @return array<string, Position>
     */
    public function getPositions(): array
    {
        return $this->positions;
    }

    public function getPosition(string $code): ?Position
    {
        return $this->positions[$code] ?? null;
    }
}
