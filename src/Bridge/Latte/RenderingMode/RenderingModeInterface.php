<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Bridge\Latte\RenderingMode;

use SixtyEightPublishers\AmpClient\Request\ValueObject\Position;

interface RenderingModeInterface
{
    public function getName(): string;

    public function supportsQueues(): bool;

    public function shouldBePositionQueued(Position $position, object $globals): bool;

    public function shouldBePositionRenderedClientSide(Position $position): bool;
}
