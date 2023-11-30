<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Bridge\Latte\RenderingMode;

use Nette\Application\UI\Presenter;
use SixtyEightPublishers\AmpClient\Request\ValueObject\Position;

final class QueuedRenderingInPresenterContextMode implements RenderingModeInterface
{
    public const Name = 'queued_in_presenter_context';

    public function getName(): string
    {
        return self::Name;
    }

    public function supportsQueues(): bool
    {
        return true;
    }

    public function shouldBePositionQueued(Position $position, object $globals): bool
    {
        return isset($globals->uiPresenter) && $globals->uiPresenter instanceof Presenter;
    }

    public function shouldBePositionRenderedClientSide(Position $position): bool
    {
        return false;
    }
}
