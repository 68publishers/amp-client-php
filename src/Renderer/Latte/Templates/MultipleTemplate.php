<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Renderer\Latte\Templates;

use SixtyEightPublishers\AmpClient\Response\ValueObject\Banner;
use SixtyEightPublishers\AmpClient\Response\ValueObject\Position;

final class MultipleTemplate
{
    public Position $position;

    /** @var array<int, Banner> */
    public array $banners;

    /**
     * @param array<int, Banner> $banners
     */
    public function __construct(
        Position $position,
        array $banners
    ) {
        $this->position = $position;
        $this->banners = $banners;
    }
}
