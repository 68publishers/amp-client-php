<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Renderer\BreakpointStyle;

final class Selector
{
    public string $selector;

    /** @var array<int, Property> */
    public array $properties = [];

    public function __construct(string $selector)
    {
        $this->selector = $selector;
    }
}
