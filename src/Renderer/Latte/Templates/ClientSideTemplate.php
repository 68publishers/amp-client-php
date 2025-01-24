<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Renderer\Latte\Templates;

use SixtyEightPublishers\AmpClient\Renderer\ClientSideMode;
use SixtyEightPublishers\AmpClient\Renderer\Options;
use SixtyEightPublishers\AmpClient\Request\ValueObject\Position;

final class ClientSideTemplate
{
    public Position $position;

    public ClientSideMode $mode;

    /** @var array<string, mixed> */
    public array $elementAttributes;

    public Options $options;

    /**
     * @param array<string, mixed> $elementAttributes
     */
    public function __construct(
        Position $position,
        ClientSideMode $mode,
        array $elementAttributes,
        Options $options
    ) {
        $this->position = $position;
        $this->mode = $mode;
        $this->elementAttributes = $elementAttributes;
        $this->options = $options;
    }
}
