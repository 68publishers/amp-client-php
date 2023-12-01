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

    /** @var array<string, scalar|null> */
    public array $elementAttributes;

    /** @var array<string, scalar> */
    public array $options;

    /**
     * @param array<int, Banner>         $banners
     * @param array<string, scalar|null> $elementAttributes
     * @param array<string, scalar>      $options
     */
    public function __construct(
        Position $position,
        array $banners,
        array $elementAttributes,
        array $options
    ) {
        $this->position = $position;
        $this->banners = $banners;
        $this->elementAttributes = $elementAttributes;
        $this->options = $options;
    }
}
