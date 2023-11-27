<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Bridge\Latte\Event;

interface ConfigureClientEventHandlerInterface
{
    public function __invoke(ConfigureClientEvent $event): ConfigureClientEvent;
}
