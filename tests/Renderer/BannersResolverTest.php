<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Tests\Renderer;

use SixtyEightPublishers\AmpClient\Renderer\BannersResolver;
use SixtyEightPublishers\AmpClient\Response\ValueObject\Banner;
use SixtyEightPublishers\AmpClient\Response\ValueObject\Dimensions;
use SixtyEightPublishers\AmpClient\Response\ValueObject\Position;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../bootstrap.php';

final class BannersResolverTest extends TestCase
{
    public function testNullShouldBeReturnedWhenResolvingSinglePositionWithoutBanners(): void
    {
        $position = new Position(
            '1234',
            'homepage.top',
            'Homepage top',
            0,
            Position::DisplayTypeSingle,
            Position::BreakpointTypeMin,
            Position::ModeManaged,
            new Dimensions(null, null),
            [],
        );
        $resolver = new BannersResolver();

        Assert::null($resolver->resolveSingle($position));
    }

    public function testFirstBannerWithHighestScoreShouldBeReturnedWhenResolvingSinglePosition(): void
    {
        $banner1 = new Banner('1', '1', 0, null, null, null, []);
        $banner2 = new Banner('2', '2', 2, null, null, null, []);
        $banner3 = new Banner('3', '3', 1, null, null, null, []);
        $banner4 = new Banner('4', '4', 2, null, null, null, []);
        $position = new Position(
            '1234',
            'homepage.top',
            'Homepage top',
            0,
            Position::DisplayTypeSingle,
            Position::BreakpointTypeMin,
            Position::ModeManaged,
            new Dimensions(null, null),
            [$banner1, $banner2, $banner3, $banner4],
        );
        $resolver = new BannersResolver();

        Assert::same($banner2, $resolver->resolveSingle($position));
    }

    public function testEmptyArrayShouldBeReturnedWhenResolvingMultiplePositionWithoutBanners(): void
    {
        $position = new Position(
            '1234',
            'homepage.top',
            'Homepage top',
            0,
            Position::DisplayTypeMultiple,
            Position::BreakpointTypeMin,
            Position::ModeManaged,
            new Dimensions(null, null),
            [],
        );
        $resolver = new BannersResolver();

        Assert::same([], $resolver->resolveMultiple($position));
    }

    public function testSortedBannersShouldBeReturnedWhenResolvingMultiplePosition(): void
    {
        $banner1 = new Banner('1', '1', 0, null, null, null, []);
        $banner2 = new Banner('2', '2', 2, null, null, null, []);
        $banner3 = new Banner('3', '3', 1, null, null, null, []);
        $banner4 = new Banner('4', '4', 2, null, null, null, []);
        $position = new Position(
            '1234',
            'homepage.top',
            'Homepage top',
            0,
            Position::DisplayTypeMultiple,
            Position::BreakpointTypeMin,
            Position::ModeManaged,
            new Dimensions(null, null),
            [$banner1, $banner2, $banner3, $banner4],
        );
        $resolver = new BannersResolver();

        Assert::same([
            $banner2,
            $banner4,
            $banner3,
            $banner1,
        ], $resolver->resolveMultiple($position));
    }

    public function testNullShouldBeReturnedWhenResolvingRandomPositionWithoutBanners(): void
    {
        $position = new Position(
            '1234',
            'homepage.top',
            'Homepage top',
            0,
            Position::DisplayTypeRandom,
            Position::BreakpointTypeMin,
            Position::ModeManaged,
            new Dimensions(null, null),
            [],
        );
        $resolver = new BannersResolver();

        Assert::null($resolver->resolveRandom($position));
    }

    public function testRandomBannerShouldBeReturnedWhenResolvingRandomPosition(): void
    {
        $banner1 = new Banner('1', '1', 1, null, null, null, []);
        $banner2 = new Banner('2', '2', 3, null, null, null, []);
        $banner3 = new Banner('3', '3', 2, null, null, null, []);
        $banner4 = new Banner('4', '4', 3, null, null, null, []);
        $position = new Position(
            '1234',
            'homepage.top',
            'Homepage top',
            0,
            Position::DisplayTypeRandom,
            Position::BreakpointTypeMin,
            Position::ModeManaged,
            new Dimensions(null, null),
            [$banner1, $banner2, $banner3, $banner4],
        );
        $resolver = new BannersResolver();

        Assert::type(Banner::class, $resolver->resolveRandom($position)); # @todo: Mock mt_rand() ?
    }
}

(new BannersResolverTest())->run();
