<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Response\ValueObject;

final class Settings
{
    private int $closeRevision;

    public function __construct(
        int $closeRevision
    ) {
        $this->closeRevision = $closeRevision;
    }

    public function getCloseRevision(): int
    {
        return $this->closeRevision;
    }
}
