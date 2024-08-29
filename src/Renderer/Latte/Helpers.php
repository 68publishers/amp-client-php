<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Renderer\Latte;

use SixtyEightPublishers\AmpClient\Renderer\Options;
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
     * @return array<string, scalar>
     */
    public static function createOptionAttributes(Options $options): array
    {
        $attributes = [];

        foreach ($options->toArray() as $name => $value) {
            $attributes['data-amp-option-' . $name] = $value;
        }

        return $attributes;
    }
}
