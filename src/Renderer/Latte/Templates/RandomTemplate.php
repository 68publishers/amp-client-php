<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Renderer\Latte\Templates;

use SixtyEightPublishers\AmpClient\Renderer\Options;
use SixtyEightPublishers\AmpClient\Response\ValueObject\Banner;
use SixtyEightPublishers\AmpClient\Response\ValueObject\Position;

final class RandomTemplate
{
    public Position $position;

    public ?Banner $banner;

    /** @var array<string, mixed> */
    public array $elementAttributes;

    public Options $options;

    /**
     * @param array<string, mixed> $elementAttributes
     */
    public function __construct(
        Position $position,
        ?Banner $banner,
        array $elementAttributes,
        Options $options
    ) {
        $this->position = $position;
        $this->banner = $banner;
        $this->elementAttributes = $elementAttributes;
        $this->options = $options;
    }
}
