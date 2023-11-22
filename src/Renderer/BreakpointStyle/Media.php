<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Renderer\BreakpointStyle;

final class Media
{
    public string $rule;

    /** @var array<int, Selector> */
    public array $selectors = [];

    public function __construct(string $rule)
    {
        $this->rule = $rule;
    }
}
