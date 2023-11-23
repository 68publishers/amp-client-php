<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Renderer\Latte\Templates;

use SixtyEightPublishers\AmpClient\Response\ValueObject\Position;

final class NotFoundTemplate
{
    public Position $position;

    public function __construct(
        Position $position
    ) {
        $this->position = $position;
    }
}
