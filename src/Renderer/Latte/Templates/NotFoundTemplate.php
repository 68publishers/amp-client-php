<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Renderer\Latte\Templates;

use SixtyEightPublishers\AmpClient\Response\ValueObject\Position;

final class NotFoundTemplate
{
    public Position $position;

    /** @var array<string, scalar|null> */
    public array $elementAttributes;

    /**
     * @param array<string, scalar|null> $elementAttributes
     */
    public function __construct(
        Position $position,
        array $elementAttributes
    ) {
        $this->position = $position;
        $this->elementAttributes = $elementAttributes;
    }
}
