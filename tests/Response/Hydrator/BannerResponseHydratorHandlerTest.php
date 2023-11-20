<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Tests\Response\Hydrator;

use SixtyEightPublishers\AmpClient\Response\BannersResponse;
use SixtyEightPublishers\AmpClient\Response\Hydrator\BannersResponseHydratorHandler;
use stdClass;
use Tester\Assert;
use Tester\TestCase;
use function file_get_contents;
use function json_decode;

require __DIR__ . '/../../bootstrap.php';

final class BannerResponseHydratorHandlerTest extends TestCase
{
    public function testCanHydrateMethod(): void
    {
        $handler = new BannersResponseHydratorHandler();

        Assert::true($handler->canHydrateResponse(BannersResponse::class));
        Assert::false($handler->canHydrateResponse(stdClass::class));
    }

    public function testResponseShouldBeHydrated(): void
    {
        $response = json_decode(file_get_contents(__DIR__ . '/../../resources/response-body/fetch-banners.full.json'), true);
        $expected = require __DIR__ . '/../../resources/response-body/fetch-banners.full.php';

        $handler = new BannersResponseHydratorHandler();

        Assert::equal($expected, $handler->hydrate($response));
    }
}

(new BannerResponseHydratorHandlerTest())->run();
