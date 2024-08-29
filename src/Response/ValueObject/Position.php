<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Response\ValueObject;

final class Position
{
    public const DisplayTypeSingle = 'single';
    public const DisplayTypeMultiple = 'multiple';
    public const DisplayTypeRandom = 'random';

    public const BreakpointTypeMin = 'min';
    public const BreakpointTypeMax = 'max';

    public const ModeManaged = 'managed';
    public const ModeEmbed = 'embed';

    private ?string $id;

    private string $code;

    private ?string $name;

    private int $rotationSeconds;

    private ?string $displayType;

    private string $breakpointType;

    private string $mode;

    /** @var array<string, string> */
    private array $options;

    private Dimensions $dimensions;

    /** @var array<int, Banner> */
    private array $banners;

    /**
     * @param array<int, Banner> $banners
     */
    public function __construct(
        ?string $id,
        string $code,
        ?string $name,
        int $rotationSeconds,
        ?string $displayType,
        string $breakpointType,
        string $mode,
        array $options,
        Dimensions $dimensions,
        array $banners
    ) {
        $this->id = $id;
        $this->code = $code;
        $this->name = $name;
        $this->rotationSeconds = $rotationSeconds;
        $this->displayType = $displayType;
        $this->breakpointType = $breakpointType;
        $this->mode = $mode;
        $this->options = $options;
        $this->dimensions = $dimensions;
        $this->banners = $banners;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getRotationSeconds(): int
    {
        return $this->rotationSeconds;
    }

    public function getDisplayType(): ?string
    {
        return $this->displayType;
    }

    public function getBreakpointType(): string
    {
        return $this->breakpointType;
    }

    public function getMode(): string
    {
        return $this->mode;
    }

    /**
     * @return array<string, string>
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    public function getDimensions(): Dimensions
    {
        return $this->dimensions;
    }

    /**
     * @return array<int, Banner>
     */
    public function getBanners(): array
    {
        return $this->banners;
    }
}
