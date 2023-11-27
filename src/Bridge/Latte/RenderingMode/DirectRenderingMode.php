<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Bridge\Latte\RenderingMode;

use SixtyEightPublishers\AmpClient\Request\ValueObject\Position;

final class DirectRenderingMode implements RenderingModeInterface
{
    public function shouldBePositionQueued(Position $position, object $globals): bool
    {
        return false;
    }
}
