<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Request\ValueObject;

use function array_merge;
use function array_unique;
use function is_array;
use function sort;

final class BannerResource
{
    private string $code;

    /** @var array<int, string> */
    private array $values;

    /**
     * @param string|array<int, string> $value
     */
    public function __construct(string $code, $value)
    {
        $this->code = $code;
        $values = is_array($value) ? array_unique($value) : [$value];

        sort($values);

        $this->values = $values;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @return array<int, string>
     */
    public function getValues(): array
    {
        return $this->values;
    }

    public function merge(self $resource): self
    {
        $values = $resource->getValues();
        $values = array_unique(array_merge($this->values, $values));

        sort($values);

        return new self(
            $this->code,
            $values,
        );
    }
}
