<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Bridge\Latte\RenderingMode;

use SixtyEightPublishers\AmpClient\Request\ValueObject\Position;

final class ClientSideRenderingMode implements RenderingModeInterface
{
    public const Name = 'client_side';

    public function getName(): string
    {
        return self::Name;
    }

    public function supportsQueues(): bool
    {
        return false;
    }

    public function shouldBePositionQueued(Position $position, object $globals): bool
    {
        return false;
    }

    public function shouldBePositionRenderedClientSide(Position $position): bool
    {
        return true;
    }
}
