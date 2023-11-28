<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Tests\Bridge\Latte\Event;

use Closure;
use SixtyEightPublishers\AmpClient\Bridge\Latte\Event\ConfigureClientEvent;
use SixtyEightPublishers\AmpClient\Bridge\Latte\Event\ConfigureClientEventHandlerInterface;

final class ConfigureClientEventHandlerFixture implements ConfigureClientEventHandlerInterface
{
    public int $invokedCount = 0;

    private ?Closure $callback;

    public function __construct(?Closure $callback)
    {
        $this->callback = $callback;
    }

    public function __invoke(ConfigureClientEvent $event): ConfigureClientEvent
    {
        ++$this->invokedCount;

        return null !== $this->callback ? ($this->callback)($event) : $event;
    }
}
