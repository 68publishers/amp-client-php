<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Response\ValueObject;

final class HtmlContent implements ContentInterface
{
    private ?int $breakpoint;

    private string $html;

    public function __construct(
        ?int $breakpoint,
        string $html
    ) {
        $this->breakpoint = $breakpoint;
        $this->html = $html;
    }

    public function getBreakpoint(): ?int
    {
        return $this->breakpoint;
    }

    public function getHtml(): string
    {
        return $this->html;
    }
}
