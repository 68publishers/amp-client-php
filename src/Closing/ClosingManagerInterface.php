<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Closing;

interface ClosingManagerInterface
{
    public function isBannerClosed(string $positionCode, string $bannerId, int $revision): bool;

    public function isPositionClosed(string $positionCode, int $revision): bool;
}
