<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Bridge\Nette\Application;

use Nette\Application\Application;
use Nette\Application\IPresenter;
use Nette\Application\UI\Presenter;
use SixtyEightPublishers\AmpClient\Bridge\Latte\RendererProvider;

final class AttachPresenterHandlersOnApplicationHandler
{
    private RendererProvider $rendererProvider;

    public function __construct(RendererProvider $rendererProvider)
    {
        $this->rendererProvider = $rendererProvider;
    }

    public static function attach(Application $application, RendererProvider $rendererProvider): void
    {
        $application->onPresenter[] = new self($rendererProvider);
    }

    public function __invoke(Application $application, IPresenter $presenter): void
    {
        if ($presenter instanceof Presenter) {
            RenderQueuedPositionsOnPresenterShutdownHandler::attach($presenter, $this->rendererProvider);
        }
    }
}
