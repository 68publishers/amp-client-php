<?php

declare(strict_types=1);

use SixtyEightPublishers\AmpClient\Response\ValueObject\Banner;
use SixtyEightPublishers\AmpClient\Response\ValueObject\HtmlContent;
use SixtyEightPublishers\AmpClient\Response\ValueObject\ImageContent;
use SixtyEightPublishers\AmpClient\Response\ValueObject\Position;
use SixtyEightPublishers\AmpClient\Response\ValueObject\Source;

$position = new Position('1234', 'homepage.top', 'Homepage top', 0, Position::DisplayTypeMultiple, Position::BreakpointTypeMin, []);

$fullFeaturedBanners = [
    new Banner('1234', 'Main', 0, null, null, null, [
        new HtmlContent(
            null,
            '<p>Small content</p>',
        ),
        new ImageContent(
            600,
            'https://www.example.com/main1',
            '_blank',
            'Main 1',
            'Main 1',
            'https://img.example.com/1000/main1.png',
            'https://img.example.com/800/main1.png 800w, https://img.example.com/1000/main1.png 1000w',
            '(min-width: 1000px) 1000px, 100vw',
            [
                new Source('image/avif', 'https://img.example.com/800/main1.avif 800w, https://img.example.com/1000/main1.avif 1000w'),
                new Source('image/webp', 'https://img.example.com/800/main1.webp 800w, https://img.example.com/1000/main1.webp 1000w'),
            ],
        ),
        new ImageContent(
            400,
            'https://www.example.com/main2',
            '_blank',
            'Main 2',
            'Main 2',
            'https://img.example.com/600/main2.png',
            'https://img.example.com/600/main2.png 600w',
            '100vw',
            [
                new Source('image/avif', 'https://img.example.com/600/main2.avif 600w'),
                new Source('image/webp', 'https://img.example.com/600/main2.webp 600w'),
            ],
        ),
    ]),
    new Banner('1235', 'Secondary', 0, null, null, null, [
        new ImageContent(
            null,
            'https://www.example.com/secondary1',
            null,
            'Secondary 1',
            'Secondary 1',
            'https://img.example.com/1000/secondary1.png',
            'https://img.example.com/800/secondary1.png 800w, https://img.example.com/1000/secondary1.png 1000w',
            '(min-width: 1000px) 1000px, 100vw',
            [],
        ),
    ]),
    new Banner('1236', 'No contents', 0, null, null, null, []),
];

return [
    'No banner' => [
        $position,
        [],
        [],
        [],
        __DIR__ . '/noBanner.html',
    ],
    'No banner with attributes' => [
        $position,
        [],
        [
            'class' => 'custom-class',
            'data-custom' => true,
            'data-custom2' => false,
            'data-custom3' => null,
        ],
        [],
        __DIR__ . '/noBanner.withAttributes.html',
    ],
    'Single banner without contents' => [
        $position,
        [
            new Banner('1234', 'Main', 0, null, null, null, []),
        ],
        [],
        [],
        __DIR__ . '/singleBannerWithoutContents.html',
    ],
    'Multiple banners without contents' => [
        $position,
        [
            new Banner('1234', 'Main 1', 0, null, null, null, []),
            new Banner('1235', 'Main 2', 0, null, null, null, []),
        ],
        [],
        [],
        __DIR__ . '/multipleBannersWithoutContents.html',
    ],
    'Multiple banners - full featured' => [
        $position,
        $fullFeaturedBanners,
        [],
        [],
        __DIR__ . '/multipleBannersFullFeatured.html',
    ],
    'Multiple banners - full featured with lazy loading (all)' => [
        $position,
        $fullFeaturedBanners,
        [],
        [
            'loading' => 'lazy',
        ],
        __DIR__ . '/multipleBannersFullFeatured.withLazyLoading.html',
    ],
    'Multiple banners - full featured with lazy loading (offset 1)' => [
        $position,
        $fullFeaturedBanners,
        [],
        [
            'loading' => 'lazy',
            'loading-offset' => 1,
        ],
        __DIR__ . '/multipleBannersFullFeatured.withLazyLoadingFromOffset1.html',
    ],
];
