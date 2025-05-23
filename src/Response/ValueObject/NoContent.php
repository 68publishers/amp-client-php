<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Response\ValueObject;

final class NoContent implements ContentInterface
{
    private ?int $breakpoint;

    public function __construct(
        ?int $breakpoint
    ) {
        $this->breakpoint = $breakpoint;
    }

    public function getBreakpoint(): ?int
    {
        return $this->breakpoint;
    }
}
