<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Response\ValueObject;

final class Source
{
    private string $type;

    private string $srcset;

    public function __construct(
        string $type,
        string $srcset
    ) {
        $this->type = $type;
        $this->srcset = $srcset;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getSrcset(): string
    {
        return $this->srcset;
    }
}
