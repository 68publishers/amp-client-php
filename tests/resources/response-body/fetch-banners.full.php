<?php

declare(strict_types=1);

use SixtyEightPublishers\AmpClient\Response\BannersResponse;
use SixtyEightPublishers\AmpClient\Response\ValueObject\Banner;
use SixtyEightPublishers\AmpClient\Response\ValueObject\Dimensions;
use SixtyEightPublishers\AmpClient\Response\ValueObject\HtmlContent;
use SixtyEightPublishers\AmpClient\Response\ValueObject\ImageContent;
use SixtyEightPublishers\AmpClient\Response\ValueObject\Position;
use SixtyEightPublishers\AmpClient\Response\ValueObject\Source;

return new BannersResponse([
    'homepage.top' => new Position(
        '0360fbe9-e742-4dee-8f3d-b63c8b601d66',
        'homepage.top',
        'Homepage top',
        3,
        Position::DisplayTypeMultiple,
        Position::BreakpointTypeMin,
        Position::ModeManaged,
        new Dimensions(
            1320,
            400,
        ),
        [
            new Banner(
                'd7275445-c287-47d2-b71a-3baff5b4d23c',
                'Homepage top - first',
                2,
                null,
                null,
                null,
                [
                    new ImageContent(
                        null,
                        'https://www.example.com/top-first',
                        '_blank',
                        'Homepage top - first',
                        'Homepage top - first',
                        'https://amp.example.com/data/images/d7275445-c287-47d2-b71a-3baff5b4d23c/w=1320/cee005030cd85b434a3b361d284011d8.jpg',
                        'https://amp.example.com/data/images/d7275445-c287-47d2-b71a-3baff5b4d23c/w=320/cee005030cd85b434a3b361d284011d8.jpg 320w, https://amp.example.com/data/images/d7275445-c287-47d2-b71a-3baff5b4d23c/w=520/cee005030cd85b434a3b361d284011d8.jpg 520w, https://amp.example.com/data/images/d7275445-c287-47d2-b71a-3baff5b4d23c/w=720/cee005030cd85b434a3b361d284011d8.jpg 720w, https://amp.example.com/data/images/d7275445-c287-47d2-b71a-3baff5b4d23c/w=920/cee005030cd85b434a3b361d284011d8.jpg 920w, https://amp.example.com/data/images/d7275445-c287-47d2-b71a-3baff5b4d23c/w=1120/cee005030cd85b434a3b361d284011d8.jpg 1120w, https://amp.example.com/data/images/d7275445-c287-47d2-b71a-3baff5b4d23c/w=1320/cee005030cd85b434a3b361d284011d8.jpg 1320w',
                        '(min-width: 1200px) calc(1200px - 2 * 16px), (min-width: 576px) calc(100vw - 2 * 16px), 100vw',
                        [
                            new Source(
                                'image/webp',
                                'https://amp.example.com/data/images/d7275445-c287-47d2-b71a-3baff5b4d23c/w=320/cee005030cd85b434a3b361d284011d8.webp 320w, https://amp.example.com/data/images/d7275445-c287-47d2-b71a-3baff5b4d23c/w=520/cee005030cd85b434a3b361d284011d8.webp 520w, https://amp.example.com/data/images/d7275445-c287-47d2-b71a-3baff5b4d23c/w=720/cee005030cd85b434a3b361d284011d8.webp 720w, https://amp.example.com/data/images/d7275445-c287-47d2-b71a-3baff5b4d23c/w=920/cee005030cd85b434a3b361d284011d8.webp 920w, https://amp.example.com/data/images/d7275445-c287-47d2-b71a-3baff5b4d23c/w=1120/cee005030cd85b434a3b361d284011d8.webp 1120w, https://amp.example.com/data/images/d7275445-c287-47d2-b71a-3baff5b4d23c/w=1320/cee005030cd85b434a3b361d284011d8.webp 1320w',
                            ),
                        ],
                    ),
                ],
            ),
            new Banner(
                '1b3f5f5a-f67d-4e0f-9d5e-b607f99fb217',
                'Homepage top - second',
                0,
                'a6c98208-b707-46c2-80c3-8f3753a522b8',
                'test-campaign',
                'Test campaign',
                [
                    new ImageContent(
                        null,
                        'https://www.example.com/top-second',
                        null,
                        'Homepage top - second',
                        'Homepage top - second',
                        'https://amp.example.com/data/images/1b3f5f5a-f67d-4e0f-9d5e-b607f99fb217/w=1320/d551946507aad54e40de5ba48fd8ed38.jpg',
                        'https://amp.example.com/data/images/1b3f5f5a-f67d-4e0f-9d5e-b607f99fb217/w=320/d551946507aad54e40de5ba48fd8ed38.jpg 320w, https://amp.example.com/data/images/1b3f5f5a-f67d-4e0f-9d5e-b607f99fb217/w=520/d551946507aad54e40de5ba48fd8ed38.jpg 520w, https://amp.example.com/data/images/1b3f5f5a-f67d-4e0f-9d5e-b607f99fb217/w=720/d551946507aad54e40de5ba48fd8ed38.jpg 720w, https://amp.example.com/data/images/1b3f5f5a-f67d-4e0f-9d5e-b607f99fb217/w=920/d551946507aad54e40de5ba48fd8ed38.jpg 920w, https://amp.example.com/data/images/1b3f5f5a-f67d-4e0f-9d5e-b607f99fb217/w=1120/d551946507aad54e40de5ba48fd8ed38.jpg 1120w, https://amp.example.com/data/images/1b3f5f5a-f67d-4e0f-9d5e-b607f99fb217/w=1320/d551946507aad54e40de5ba48fd8ed38.jpg 1320w',
                        '(min-width: 1200px) calc(1200px - 2 * 16px), (min-width: 576px) calc(100vw - 2 * 16px), 100vw',
                        [
                            new Source(
                                'image/webp',
                                'https://amp.example.com/data/images/1b3f5f5a-f67d-4e0f-9d5e-b607f99fb217/w=320/d551946507aad54e40de5ba48fd8ed38.webp 320w, https://amp.example.com/data/images/1b3f5f5a-f67d-4e0f-9d5e-b607f99fb217/w=520/d551946507aad54e40de5ba48fd8ed38.webp 520w, https://amp.example.com/data/images/1b3f5f5a-f67d-4e0f-9d5e-b607f99fb217/w=720/d551946507aad54e40de5ba48fd8ed38.webp 720w, https://amp.example.com/data/images/1b3f5f5a-f67d-4e0f-9d5e-b607f99fb217/w=920/d551946507aad54e40de5ba48fd8ed38.webp 920w, https://amp.example.com/data/images/1b3f5f5a-f67d-4e0f-9d5e-b607f99fb217/w=1120/d551946507aad54e40de5ba48fd8ed38.webp 1120w, https://amp.example.com/data/images/1b3f5f5a-f67d-4e0f-9d5e-b607f99fb217/w=1320/d551946507aad54e40de5ba48fd8ed38.webp 1320w',
                            ),
                        ],
                    ),
                ],
            ),
        ],
    ),
    'homepage.middle' => new Position(
        '82fde136-1ba5-4556-b7f1-5d7e870f08d1',
        'homepage.middle',
        'Homepage - middle',
        5,
        Position::DisplayTypeRandom,
        Position::BreakpointTypeMin,
        Position::ModeManaged,
        new Dimensions(
            800,
            300,
        ),
        [],
    ),
    'homepage.missing' => new Position(
        null,
        'homepage.missing',
        null,
        0,
        null,
        Position::BreakpointTypeMin,
        Position::ModeManaged,
        new Dimensions(
            null,
            null,
        ),
        [],
    ),
    'homepage.bottom' => new Position(
        'ac13dfd6-547f-4f5e-801f-5799ce24ee09',
        'homepage.bottom',
        'Homepage - bottom',
        5,
        Position::DisplayTypeSingle,
        Position::BreakpointTypeMin,
        Position::ModeManaged,
        new Dimensions(
            800,
            300,
        ),
        [
            new Banner(
                '54c72f46-2b6c-4d80-a75e-28fed4d79f5c',
                'Homepage bottom',
                0,
                'a6c98208-b707-46c2-80c3-8f3753a522b8',
                'test-campaign',
                'Test campaign',
                [
                    new ImageContent(
                        null,
                        'https://www.example.com/bottom',
                        '_blank',
                        'Homepage bottom',
                        'Homepage bottom',
                        'https://amp.example.com/data/images/54c72f46-2b6c-4d80-a75e-28fed4d79f5c/w=500/462316f2-30f2-4eb5-ad0d-dbc06ef0e06b.jpg',
                        'https://amp.example.com/data/images/54c72f46-2b6c-4d80-a75e-28fed4d79f5c/w=320/462316f2-30f2-4eb5-ad0d-dbc06ef0e06b.jpg 320w, https://amp.example.com/data/images/54c72f46-2b6c-4d80-a75e-28fed4d79f5c/w=520/462316f2-30f2-4eb5-ad0d-dbc06ef0e06b.jpg 520w',
                        '100vw',
                        [],
                    ),
                    new HtmlContent(
                        500,
                        '<div class="alert alert-info w-100">Homepage bottom</div>',
                    ),
                ],
            ),
        ],
    ),
]);
