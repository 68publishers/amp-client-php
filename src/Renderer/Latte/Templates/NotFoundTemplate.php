<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Renderer\Latte\Templates;

use SixtyEightPublishers\AmpClient\Response\ValueObject\Position;

final class NotFoundTemplate
{
    public Position $position;

    /** @var array<string, scalar|null> */
    public array $elementAttributes;

    /** @var array<string, scalar> */
    public array $options;

    /**
     * @param array<string, scalar|null> $elementAttributes
     * @param array<string, scalar>      $options
     */
    public function __construct(
        Position $position,
        array $elementAttributes,
        array $options
    ) {
        $this->position = $position;
        $this->elementAttributes = $elementAttributes;
        $this->options = $options;
    }
}
