<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Bridge\Latte\RenderingMode;

use Nette\Application\UI\Presenter;
use SixtyEightPublishers\AmpClient\Request\ValueObject\Position;

final class QueuedRenderingInPresenterContextMode implements RenderingModeInterface
{
    public function shouldBePositionQueued(Position $position, object $globals): bool
    {
        return isset($globals->uiPresenter) && $globals->uiPresenter instanceof Presenter;
    }
}
