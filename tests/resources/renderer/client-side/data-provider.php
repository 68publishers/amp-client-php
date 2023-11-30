<?php

declare(strict_types=1);

use SixtyEightPublishers\AmpClient\Request\ValueObject\BannerResource;
use SixtyEightPublishers\AmpClient\Request\ValueObject\Position;

return [
    'Position only' => [
        new Position('homepage.top'),
        [],
        __DIR__ . '/positionOnly.html',
    ],
    'With resources' => [
        new Position('homepage.top', [
            new BannerResource('role', 'vip'),
            new BannerResource('category', [123, 456]),
        ]),
        [],
        __DIR__ . '/withResources.html',
    ],
    'With attributes' => [
        new Position('homepage.top'),
        [
            'class' => 'custom-class',
            'data-custom' => true,
            'data-custom2' => false,
            'data-custom3' => null,
        ],
        __DIR__ . '/withAttributes.html',
    ],
    'With resources and attributes' => [
        new Position('homepage.top', [
            new BannerResource('role', 'vip'),
            new BannerResource('category', [123, 456]),
        ]),
        [
            'class' => 'custom-class',
            'data-custom' => true,
            'data-custom2' => false,
            'data-custom3' => null,
        ],
        __DIR__ . '/withResourcesAndAttributes.html',
    ],
];
