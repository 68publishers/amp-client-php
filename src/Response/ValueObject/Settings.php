<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Response\ValueObject;

final class Settings
{
    private int $closedRevision;

    public function __construct(
        int $closedRevision
    ) {
        $this->closedRevision = $closedRevision;
    }

    public function getClosedRevision(): int
    {
        return $this->closedRevision;
    }
}
