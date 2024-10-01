<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Response\ValueObject;

final class ImageContent implements ContentInterface
{
    private ?int $breakpoint;

    private string $href;

    private ?string $target;

    private string $alt;

    private string $title;

    private string $src;

    private string $srcset;

    private string $sizes;

    /** @var array<int, Source> */
    private array $sources;

    private Dimensions $dimensions;

    /**
     * @param array<int, Source> $sources
     */
    public function __construct(
        ?int $breakpoint,
        string $href,
        ?string $target,
        string $alt,
        string $title,
        string $src,
        string $srcset,
        string $sizes,
        array $sources,
        Dimensions $dimensions
    ) {
        $this->breakpoint = $breakpoint;
        $this->href = $href;
        $this->target = $target;
        $this->alt = $alt;
        $this->title = $title;
        $this->src = $src;
        $this->srcset = $srcset;
        $this->sizes = $sizes;
        $this->sources = $sources;
        $this->dimensions = $dimensions;
    }

    public function getBreakpoint(): ?int
    {
        return $this->breakpoint;
    }

    public function getHref(): string
    {
        return $this->href;
    }

    public function getTarget(): ?string
    {
        return $this->target;
    }

    public function getAlt(): string
    {
        return $this->alt;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getSrc(): string
    {
        return $this->src;
    }

    public function getSrcset(): string
    {
        return $this->srcset;
    }

    public function getSizes(): string
    {
        return $this->sizes;
    }

    /**
     * @return array<int, Source>
     */
    public function getSources(): array
    {
        return $this->sources;
    }

    public function getDimensions(): Dimensions
    {
        return $this->dimensions;
    }
}
