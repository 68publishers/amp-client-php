<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Renderer\Latte;

use SixtyEightPublishers\AmpClient\Request\ValueObject\Position as RequestPosition;

final class Helpers
{
    private function __construct() {}

    /**
     * @return array<string, string>
     */
    public static function createResourceAttributes(RequestPosition $position): array
    {
        $attributes = [];

        foreach ($position->getResources() as $resource) {
            $attributes['data-amp-resource-' . $resource->getCode()] = implode(',', $resource->getValues());
        }

        return $attributes;
    }

    /**
     * @param array<string, scalar> $options
     *
     * @return array<string, scalar>
     */
    public static function createOptionAttributes(array $options): array
    {
        $attributes = [];

        foreach ($options as $name => $value) {
            $attributes['data-amp-option-' . $name] = $value;
        }

        return $attributes;
    }
}
