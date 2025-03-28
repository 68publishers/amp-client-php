<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Renderer\Latte\Templates;

use SixtyEightPublishers\AmpClient\Renderer\Options;
use SixtyEightPublishers\AmpClient\Response\ValueObject\Position;

final class ClosedTemplate
{
    public Position $position;

    /** @var array<string, mixed> */
    public array $elementAttributes;

    public Options $options;

    /**
     * @param array<string, mixed> $elementAttributes
     */
    public function __construct(
        Position $position,
        array $elementAttributes,
        Options $options
    ) {
        $this->position = $position;
        $this->elementAttributes = $elementAttributes;
        $this->options = $options;
    }
}
