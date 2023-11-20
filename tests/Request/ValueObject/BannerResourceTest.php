<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Tests\Request\ValueObject;

use SixtyEightPublishers\AmpClient\Request\ValueObject\BannerResource;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../bootstrap.php';

final class BannerResourceTest extends TestCase
{
    public function testCreatingResourceWithSingleValue(): void
    {
        $resource = new BannerResource('test', 'a');

        Assert::same('test', $resource->getCode());
        Assert::same(['a'], $resource->getValues());
    }

    public function testCreatingResourceWithMultipleValues(): void
    {
        $resource = new BannerResource('test', ['b', 'a', 'c', 'a']);

        Assert::same('test', $resource->getCode());
        Assert::same(['a', 'b', 'c'], $resource->getValues());
    }

    public function testResourcesMerging(): void
    {
        $resource1 = new BannerResource('test', ['a', 'x']);
        $resource2 = new BannerResource('test2', ['b', 'a', 'c', 'c']);

        $merged1 = $resource1->merge($resource2);
        $merged2 = $resource2->merge($resource1);

        Assert::same('test', $merged1->getCode());
        Assert::same('test2', $merged2->getCode());

        Assert::same(['a', 'b', 'c', 'x'], $merged1->getValues());
        Assert::same(['a', 'b', 'c', 'x'], $merged2->getValues());
    }
}

(new BannerResourceTest())->run();
