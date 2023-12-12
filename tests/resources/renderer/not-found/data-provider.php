<?php

declare(strict_types=1);

use SixtyEightPublishers\AmpClient\Response\ValueObject\Position;
use SixtyEightPublishers\AmpClient\Response\ValueObject\Position as ResponsePosition;

$position = new Position(null, 'homepage.top', null, 0, null, Position::BreakpointTypeMin, ResponsePosition::ModeManaged, []);

return [
    'Not found' => [
        $position,
        [],
        [],
        __DIR__ . '/notFound.html',
    ],
    'Not found with attributes' => [
        $position,
        [
            'class' => 'custom-class',
            'data-custom' => true,
            'data-custom2' => false,
            'data-custom3' => null,
        ],
        [],
        __DIR__ . '/notFound.withAttributes.html',
    ],
    'Not found with lazy loading' => [
        $position,
        [],
        [
            'loading' => 'lazy',
        ],
        __DIR__ . '/notFound.withLazyLoading.html',
    ],
];
