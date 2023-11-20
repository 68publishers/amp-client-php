<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Response;

use SixtyEightPublishers\AmpClient\Response\ValueObject\Position;

final class BannersResponse
{
    /** @var array<string, Position> */
    private array $positions;

    /**
     * @param array<string, Position> $positions
     */
    public function __construct(array $positions)
    {
        $this->positions = $positions;
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
