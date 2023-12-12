<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Request;

use InvalidArgumentException;
use SixtyEightPublishers\AmpClient\Request\ValueObject\Position;
use function sprintf;

final class BannersRequest
{
    /** @var array<string, Position> */
    private array $positions = [];

    private ?string $locale;

    /**
     * @param array<int, Position> $positions
     */
    public function __construct(array $positions = [], ?string $locale = null)
    {
        foreach ($positions as $position) {
            $this->assertPositionCodeDoesNotExists($position->getCode());

            $this->positions[$position->getCode()] = $position;
        }

        $this->locale = $locale;
    }

    public function withPosition(Position $position): self
    {
        $this->assertPositionCodeDoesNotExists($position->getCode());

        $request = clone $this;
        $request->positions[$position->getCode()] = $position;

        return $request;
    }

    public function withLocale(string $locale): self
    {
        $request = clone $this;
        $request->locale = $locale;

        return $request;
    }

    /**
     * @return array<string, Position>
     */
    public function getPositions(): array
    {
        return $this->positions;
    }

    public function getPosition(string $name): ?Position
    {
        return $this->positions[$name] ?? null;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function assertPositionCodeDoesNotExists(string $code): void
    {
        if (isset($this->positions[$code])) {
            throw new InvalidArgumentException(sprintf(
                'Position "%s" has been already defined.',
                $code,
            ));
        }
    }
}
