<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Renderer\Latte\Templates;

use SixtyEightPublishers\AmpClient\Renderer\Options;
use SixtyEightPublishers\AmpClient\Response\ValueObject\Banner;
use SixtyEightPublishers\AmpClient\Response\ValueObject\Position;

final class MultipleTemplate
{
    public Position $position;

    /** @var array<int, Banner> */
    public array $banners;

    /** @var array<string, scalar|null> */
    public array $elementAttributes;

    public Options $options;

    /**
     * @param array<int, Banner>         $banners
     * @param array<string, scalar|null> $elementAttributes
     */
    public function __construct(
        Position $position,
        array $banners,
        array $elementAttributes,
        Options $options
    ) {
        $this->position = $position;
        $this->banners = $banners;
        $this->elementAttributes = $elementAttributes;
        $this->options = $options;
    }
}
