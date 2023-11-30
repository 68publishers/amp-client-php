<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Renderer\Latte\Templates;

use SixtyEightPublishers\AmpClient\Request\ValueObject\Position;

final class ClientSideTemplate
{
    public Position $position;

    /** @var array<string, string> */
    public array $resourceAttributes;

    /** @var array<string, scalar|null> */
    public array $elementAttributes;

    /**
     * @param array<string, string>      $resourceAttributes
     * @param array<string, scalar|null> $elementAttributes
     */
    public function __construct(
        Position $position,
        array $resourceAttributes,
        array $elementAttributes
    ) {
        $this->position = $position;
        $this->resourceAttributes = $resourceAttributes;
        $this->elementAttributes = $elementAttributes;
    }
}
