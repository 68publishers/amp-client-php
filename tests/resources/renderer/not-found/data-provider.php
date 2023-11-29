<?php

declare(strict_types=1);

use SixtyEightPublishers\AmpClient\Response\ValueObject\Position;

$position = new Position(null, 'homepage.top', null, 0, null, Position::BreakpointTypeMin, []);

return [
    'Not found' => [
        $position,
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
        __DIR__ . '/notFound.withAttributes.html',
    ],
];
