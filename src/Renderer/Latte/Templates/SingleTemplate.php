<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Renderer\Latte\Templates;

use SixtyEightPublishers\AmpClient\Response\ValueObject\Banner;
use SixtyEightPublishers\AmpClient\Response\ValueObject\Position;

final class SingleTemplate
{
    public Position $position;

    public ?Banner $banner;

    /** @var array<string, scalar|null> */
    public array $elementAttributes;

    /**
     * @param array<string, scalar|null> $elementAttributes
     */
    public function __construct(
        Position $position,
        ?Banner $banner,
        array $elementAttributes
    ) {
        $this->position = $position;
        $this->banner = $banner;
        $this->elementAttributes = $elementAttributes;
    }
}
