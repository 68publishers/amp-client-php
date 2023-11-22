<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Renderer;

use SixtyEightPublishers\AmpClient\Response\ValueObject\Banner;
use SixtyEightPublishers\AmpClient\Response\ValueObject\Position;

interface BannersResolverInterface
{
    public function resolveSingle(Position $position): ?Banner;

    public function resolveRandom(Position $position): ?Banner;

    /**
     * @return array<int, Banner>
     */
    public function resolveMultiple(Position $position): array;
}
