<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Renderer\BreakpointStyle;

final class Property
{
    public string $name;

    public string $value;

    public function __construct(string $name, string $value)
    {
        $this->name = $name;
        $this->value = $value;
    }
}
