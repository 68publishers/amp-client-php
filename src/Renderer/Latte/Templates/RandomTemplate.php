<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Renderer\Latte\Templates;

use SixtyEightPublishers\AmpClient\Response\ValueObject\Banner;
use SixtyEightPublishers\AmpClient\Response\ValueObject\Position;

final class RandomTemplate
{
    public Position $position;

    public ?Banner $banner;

    public function __construct(
        Position $position,
        ?Banner $banner
    ) {
        $this->position = $position;
        $this->banner = $banner;
    }
}
