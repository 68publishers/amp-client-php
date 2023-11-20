<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Tests\Request\ValueObject;

use SixtyEightPublishers\AmpClient\Request\ValueObject\BannerResource;
use SixtyEightPublishers\AmpClient\Request\ValueObject\Position;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../bootstrap.php';

final class PositionTest extends TestCase
{
    public function testCreatingPositionWithoutResources(): void
    {
        $position = new Position('test');

        Assert::same('test', $position->getCode());
        Assert::same([], $position->getResources());
    }

    public function testCreatingPositionWithResources(): void
    {
        $position = new Position('test', [
            new BannerResource('test1', 'a'),
            new BannerResource('test2', ['a', 'c', 'b']),
            new BannerResource('test1', ['b']),
        ]);

        Assert::same('test', $position->getCode());
        Assert::equal([
            'test1' => new BannerResource('test1', ['a', 'b']),
            'test2' => new BannerResource('test2', ['a', 'b', 'c']),
        ], $position->getResources());
    }

    public function testAddingPositionResources(): void
    {
        $position = new Position('test', [
            new BannerResource('test1', 'a'),
        ]);

        $position2 = $position->withResources([
            new BannerResource('test2', ['a', 'b']),
            new BannerResource('test1', ['a', 'b', 'c']),
        ]);

        Assert::notSame($position, $position2);
        Assert::same('test', $position->getCode());
        Assert::same('test', $position->getCode());
        Assert::equal([
            'test1' => new BannerResource('test1', ['a']),
        ], $position->getResources());
        Assert::equal([
            'test1' => new BannerResource('test1', ['a', 'b', 'c']),
            'test2' => new BannerResource('test2', ['a', 'b']),
        ], $position2->getResources());
    }
}

(new PositionTest())->run();
