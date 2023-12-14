<?php

declare(strict_types=1);

use SixtyEightPublishers\AmpClient\Renderer\ClientSideMode;
use SixtyEightPublishers\AmpClient\Request\ValueObject\BannerResource;
use SixtyEightPublishers\AmpClient\Request\ValueObject\Position;

return [
    'Position only (managed)' => [
        new Position('homepage.top'),
        [],
        [],
        ClientSideMode::managed(),
        __DIR__ . '/managed.positionOnly.html',
    ],
    'Position only (embed)' => [
        new Position('homepage.top'),
        [],
        [],
        ClientSideMode::embed(),
        __DIR__ . '/embed.positionOnly.html',
    ],
    'With resources (managed)' => [
        new Position('homepage.top', [
            new BannerResource('role', 'vip'),
            new BannerResource('category', [123, 456]),
        ]),
        [],
        [],
        ClientSideMode::managed(),
        __DIR__ . '/managed.withResources.html',
    ],
    'With resources (embed)' => [
        new Position('homepage.top', [
            new BannerResource('role', 'vip'),
            new BannerResource('category', [123, 456]),
        ]),
        [],
        [],
        ClientSideMode::embed(),
        __DIR__ . '/embed.withResources.html',
    ],
    'With attributes (managed)' => [
        new Position('homepage.top'),
        [
            'class' => 'custom-class',
            'data-custom' => true,
            'data-custom2' => false,
            'data-custom3' => null,
        ],
        [],
        ClientSideMode::managed(),
        __DIR__ . '/managed.withAttributes.html',
    ],
    'With attributes (embed)' => [
        new Position('homepage.top'),
        [
            'class' => 'custom-class',
            'data-custom' => true,
            'data-custom2' => false,
            'data-custom3' => null,
        ],
        [],
        ClientSideMode::embed(),
        __DIR__ . '/embed.withAttributes.html',
    ],
    'With lazy loading (managed)' => [
        new Position('homepage.top'),
        [],
        [
            'loading' => 'lazy',
        ],
        ClientSideMode::managed(),
        __DIR__ . '/managed.withLazyLoading.html',
    ],
    'With lazy loading (embed)' => [
        new Position('homepage.top'),
        [],
        [
            'loading' => 'lazy',
        ],
        ClientSideMode::embed(),
        __DIR__ . '/embed.withLazyLoading.html',
    ],
    'Full featured (managed)' => [
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
        [
            'loading' => 'lazy',
            'custom' => 'value',
        ],
        ClientSideMode::managed(),
        __DIR__ . '/managed.fullFeatured.html',
    ],
    'Full featured (embed)' => [
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
        [
            'loading' => 'lazy',
            'custom' => 'value',
        ],
        ClientSideMode::embed(),
        __DIR__ . '/embed.fullFeatured.html',
    ],
];
