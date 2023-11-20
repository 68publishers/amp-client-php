<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Tests\Http\Cache;

use GuzzleHttp\Psr7\Response;
use SixtyEightPublishers\AmpClient\Http\Cache\Etag;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../bootstrap.php';

final class EtagTest extends TestCase
{
    public function testCreatingEtagFromString(): void
    {
        $etag = new Etag('test');

        Assert::same('test', $etag->getValue());
    }

    public function testCreatingEtagFromResponse(): void
    {
        $response = new Response(200, [
            'ETag' => 'test',
        ]);

        $etag = Etag::fromResponse($response);

        Assert::same('test', $etag->getValue());
    }
}

(new EtagTest())->run();
