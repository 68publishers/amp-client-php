<?php

declare(strict_types=1);

use SixtyEightPublishers\AmpClient\Response\ValueObject\Banner;
use SixtyEightPublishers\AmpClient\Response\ValueObject\Dimensions;
use SixtyEightPublishers\AmpClient\Response\ValueObject\HtmlContent;
use SixtyEightPublishers\AmpClient\Response\ValueObject\ImageContent;
use SixtyEightPublishers\AmpClient\Response\ValueObject\Position;
use SixtyEightPublishers\AmpClient\Response\ValueObject\Position as ResponsePosition;
use SixtyEightPublishers\AmpClient\Response\ValueObject\Source;

$position = new Position(
    '1234',
    'homepage.top',
    'Homepage top',
    0,
    Position::DisplayTypeRandom,
    Position::BreakpointTypeMin,
    ResponsePosition::ModeManaged,
    null,
    [],
    [],
);

return [
    'No banner' => [
        $position,
        null,
        [],
        [],
        __DIR__ . '/noBanner.html',
    ],
    'No banner with attributes' => [
        $position,
        null,
        [
            'class' => 'custom-class',
            'data-custom' => true,
            'data-custom2' => false,
            'data-custom3' => null,
        ],
        [],
        __DIR__ . '/noBanner.withAttributes.html',
    ],
    'Banner without contents' => [
        $position,
        new Banner('1234', 'Main', 0, null, null, null, null, []),
        [],
        [],
        __DIR__ . '/bannerWithoutContent.html',
    ],
    'Banner with default content only: image without optional values' => [
        $position,
        new Banner('1234', 'Main', 0, null, null, null, null, [
            new ImageContent(
                null,
                'https://www.example.com/main1',
                null,
                'Main 1',
                'Main 1',
                'https://img.example.com/1000/main1.png',
                'https://img.example.com/500/main1.png 500w, https://img.example.com/1000/main1.png 1000w',
                '(min-width: 1000px) calc(1000px - 2 * 16px), (min-width: 600px) calc(100vw - 2 * 16px), 100vw',
                [],
                new Dimensions(1000, 300),
            ),
        ]),
        [],
        [],
        __DIR__ . '/bannerWithDefaultImageContentOnly.withoutOptionalValues.html',
    ],
    'Banner with default content only: image without dimensions' => [
        $position,
        new Banner('1234', 'Main', 0, null, null, null, null, [
            new ImageContent(
                null,
                'https://www.example.com/main1',
                null,
                'Main 1',
                'Main 1',
                'https://img.example.com/1000/main1.png',
                'https://img.example.com/500/main1.png 500w, https://img.example.com/1000/main1.png 1000w',
                '(min-width: 1000px) calc(1000px - 2 * 16px), (min-width: 600px) calc(100vw - 2 * 16px), 100vw',
                [],
                new Dimensions(null, null),
            ),
        ]),
        [],
        [],
        __DIR__ . '/bannerWithDefaultImageContentOnly.withoutDimensions.html',
    ],
    'Banner with default content only: image with optional value' => [
        $position,
        new Banner('1234', 'Main', 0, null, null, null, null, [
            new ImageContent(
                null,
                'https://www.example.com/main1',
                '_blank',
                'Main 1',
                'Main 1',
                'https://img.example.com/1000/main1.png',
                'https://img.example.com/500/main1.png 500w, https://img.example.com/1000/main1.png 1000w',
                '(min-width: 1000px) calc(1000px - 2 * 16px), (min-width: 600px) calc(100vw - 2 * 16px), 100vw',
                [
                    new Source('image/avif', 'https://img.example.com/500/main1.avif 500w, https://img.example.com/1000/main1.avif 1000w'),
                    new Source('image/webp', 'https://img.example.com/500/main1.webp 500w, https://img.example.com/1000/main1.webp 1000w'),
                ],
                new Dimensions(1000, 300),
            ),
        ]),
        [],
        [],
        __DIR__ . '/bannerWithDefaultImageContentOnly.withOptionalValues.html',
    ],
    'Banner with default content only: image with lazy loading' => [
        $position,
        new Banner('1234', 'Main', 0, null, null, null, null, [
            new ImageContent(
                null,
                'https://www.example.com/main1',
                '_blank',
                'Main 1',
                'Main 1',
                'https://img.example.com/1000/main1.png',
                'https://img.example.com/500/main1.png 500w, https://img.example.com/1000/main1.png 1000w',
                '(min-width: 1000px) calc(1000px - 2 * 16px), (min-width: 600px) calc(100vw - 2 * 16px), 100vw',
                [
                    new Source('image/avif', 'https://img.example.com/500/main1.avif 500w, https://img.example.com/1000/main1.avif 1000w'),
                    new Source('image/webp', 'https://img.example.com/500/main1.webp 500w, https://img.example.com/1000/main1.webp 1000w'),
                ],
                new Dimensions(1000, 300),
            ),
        ]),
        [],
        [
            'loading' => 'lazy',
        ],
        __DIR__ . '/bannerWithDefaultImageContentOnly.withLazyLoading.html',
    ],
    'Banner with breakpoint content only: image' => [
        $position,
        new Banner('1234', 'Main', 0, null, null, null, null, [
            new ImageContent(
                500,
                'https://www.example.com/main1',
                null,
                'Main 1',
                'Main 1',
                'https://img.example.com/1000/main1.png',
                'https://img.example.com/500/main1.png 500w, https://img.example.com/1000/main1.png 1000w',
                '(min-width: 1000px) calc(1000px - 2 * 16px), (min-width: 600px) calc(100vw - 2 * 16px), 100vw',
                [],
                new Dimensions(1000, 300),
            ),
        ]),
        [],
        [],
        __DIR__ . '/bannerWithBreakpointImageContentOnly.html',
    ],
    'Banner with default content only: html' => [
        $position,
        new Banner('1234', 'Main', 0, null, null, null, null, [
            new HtmlContent(
                null,
                '<p>My <span style="color:red;">Awesome</span> content!</p>',
            ),
        ]),
        [],
        [],
        __DIR__ . '/bannerWithDefaultHtmlContentOnly.html',
    ],
    'Banner with breakpoint content only: html' => [
        $position,
        new Banner('1234', 'Main', 0, null, null, null, null, [
            new HtmlContent(
                500,
                '<p>My <span style="color:red;">Awesome</span> content!</p>',
            ),
        ]),
        [],
        [],
        __DIR__ . '/bannerWithBreakpointHtmlContentOnly.html',
    ],
    'Banner with multiple contents' => [
        $position,
        new Banner('1234', 'Main', 0, null, null, null, null, [
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
                new Dimensions(1000, 300),
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
                new Dimensions(600, 300),
            ),
        ]),
        [],
        [
            'loading' => 'lazy',
        ],
        __DIR__ . '/bannerWithMultipleContents.html',
    ],
];
