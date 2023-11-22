<?php

declare(strict_types=1);

use SixtyEightPublishers\AmpClient\Response\ValueObject\Position;

return [
    'Not found' => [
        new Position(null, 'homepage.top', null, 0, null, Position::BreakpointTypeMin, []),
        __DIR__ . '/notFound.html',
    ],
];
