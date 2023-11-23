<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Renderer\Latte;

use Latte\Engine;

interface LatteFactoryInterface
{
    public function create(): Engine;
}
