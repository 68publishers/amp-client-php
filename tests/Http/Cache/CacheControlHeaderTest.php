<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Tests\Http\Cache;

use GuzzleHttp\Psr7\Response;
use SixtyEightPublishers\AmpClient\Http\Cache\CacheControlHeader;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../bootstrap.php';

final class CacheControlHeaderTest extends TestCase
{
    public function testHeaderParsingFromArray(): void
    {
        $header = new CacheControlHeader([
            'max-age=300, must-revalidate',
            'no-cache, no-store, must-revalidate',
            's-maxage=100',
        ]);

        $this->doAsserts($header);
    }

    public function testHeaderParsingFromResponse(): void
    {
        $response = new Response(200, [
            'Cache-Control' => [
                'max-age=300, must-revalidate',
                'no-cache, no-store, must-revalidate',
                's-maxage=100',
            ],
        ]);

        $header = CacheControlHeader::fromResponse($response);

        $this->doAsserts($header);
    }

    private function doAsserts(CacheControlHeader $header): void
    {
        Assert::true($header->has('max-age'));
        Assert::true($header->has('must-revalidate'));
        Assert::true($header->has('no-cache'));
        Assert::true($header->has('no-store'));
        Assert::true($header->has('must-revalidate'));
        Assert::true($header->has('s-maxage'));
        Assert::false($header->has('foo'));

        Assert::same('300', $header->get('max-age'));
        Assert::same('', $header->get('must-revalidate'));
        Assert::same('', $header->get('no-cache'));
        Assert::same('', $header->get('no-store'));
        Assert::same('', $header->get('must-revalidate'));
        Assert::same('100', $header->get('s-maxage'));

        Assert::same([
            'max-age' => '300',
            'must-revalidate' => '',
            'no-cache' => '',
            'no-store' => '',
            's-maxage' => '100',
        ], $header->all());

        Assert::false($header->isEmpty());
    }
}

(new CacheControlHeaderTest())->run();
