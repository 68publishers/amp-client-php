<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Renderer\Latte\Templates;

use SixtyEightPublishers\AmpClient\Renderer\ClientSideMode;
use SixtyEightPublishers\AmpClient\Request\ValueObject\Position;

final class ClientSideTemplate
{
    public Position $position;

    public ClientSideMode $mode;

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
        ClientSideMode $mode,
        array $elementAttributes,
        array $options
    ) {
        $this->position = $position;
        $this->mode = $mode;
        $this->elementAttributes = $elementAttributes;
        $this->options = $options;
    }
}
