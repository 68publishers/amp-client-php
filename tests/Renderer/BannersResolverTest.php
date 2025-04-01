<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Tests\Renderer;

use Mockery;
use SixtyEightPublishers\AmpClient\Closing\ClosingManagerInterface;
use SixtyEightPublishers\AmpClient\Renderer\BannersResolver;
use SixtyEightPublishers\AmpClient\Response\ValueObject\Banner;
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
            null,
            [],
            [],
        );
        $resolver = new BannersResolver(
            $this->createClosingManager(
                'homepage.top',
                false,
                [],
                1,
            ),
        );

        Assert::null($resolver->resolveSingle($position, 1));
    }

    public function testNullShouldBeReturnedWhenResolvingClosedSinglePosition(): void
    {
        $position = new Position(
            '1234',
            'homepage.top',
            'Homepage top',
            0,
            Position::DisplayTypeSingle,
            Position::BreakpointTypeMin,
            Position::ModeManaged,
            null,
            [],
            [
                new Banner('1', '1', 0, null, null, null, null, []),
            ],
        );
        $resolver = new BannersResolver(
            $this->createClosingManager(
                'homepage.top',
                true,
                [],
                1,
            ),
        );

        Assert::null($resolver->resolveSingle($position, 1));
    }

    public function testFirstBannerWithHighestScoreShouldBeReturnedWhenResolvingSinglePosition(): void
    {
        $banner1 = new Banner('1', '1', 0, null, null, null, null, []);
        $banner2 = new Banner('2', '2', 2, null, null, null, null, []);
        $banner3 = new Banner('3', '3', 1, null, null, null, null, []);
        $banner4 = new Banner('4', '4', 2, null, null, null, null, []);
        $position = new Position(
            '1234',
            'homepage.top',
            'Homepage top',
            0,
            Position::DisplayTypeSingle,
            Position::BreakpointTypeMin,
            Position::ModeManaged,
            null,
            [],
            [$banner1, $banner2, $banner3, $banner4],
        );
        $resolver = new BannersResolver(
            $this->createClosingManager(
                'homepage.top',
                false,
                [
                    '1' => false,
                    '2' => false,
                    '3' => false,
                    '4' => false,
                ],
                1,
            ),
        );

        Assert::same($banner2, $resolver->resolveSingle($position, 1));
    }

    public function testFirstBannerWithHighestScoreShouldBeReturnedWhenResolvingSinglePositionWithSomeClosedBanners(): void
    {
        $banner1 = new Banner('1', '1', 0, null, null, null, null, []);
        $banner2 = new Banner('2', '2', 2, null, null, null, null, []);
        $banner3 = new Banner('3', '3', 3, null, null, null, null, []);
        $banner4 = new Banner('4', '4', 2, null, null, null, null, []);
        $position = new Position(
            '1234',
            'homepage.top',
            'Homepage top',
            0,
            Position::DisplayTypeSingle,
            Position::BreakpointTypeMin,
            Position::ModeManaged,
            null,
            [],
            [$banner1, $banner2, $banner3, $banner4],
        );
        $resolver = new BannersResolver(
            $this->createClosingManager(
                'homepage.top',
                false,
                [
                    '1' => false,
                    '2' => true,
                    '3' => true,
                    '4' => false,
                ],
                1,
            ),
        );

        Assert::same($banner4, $resolver->resolveSingle($position, 1));
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
            null,
            [],
            [],
        );
        $resolver = new BannersResolver(
            $this->createClosingManager(
                'homepage.top',
                false,
                [],
                1,
            ),
        );

        Assert::same([], $resolver->resolveMultiple($position, 1));
    }

    public function testEmptyArrayShouldBeReturnedWhenResolvingClosedMultiplePosition(): void
    {
        $position = new Position(
            '1234',
            'homepage.top',
            'Homepage top',
            0,
            Position::DisplayTypeMultiple,
            Position::BreakpointTypeMin,
            Position::ModeManaged,
            null,
            [],
            [
                new Banner('1', '1', 0, null, null, null, null, []),
            ],
        );
        $resolver = new BannersResolver(
            $this->createClosingManager(
                'homepage.top',
                true,
                [],
                1,
            ),
        );

        Assert::same([], $resolver->resolveMultiple($position, 1));
    }

    public function testSortedBannersShouldBeReturnedWhenResolvingMultiplePosition(): void
    {
        $banner1 = new Banner('1', '1', 0, null, null, null, null, []);
        $banner2 = new Banner('2', '2', 2, null, null, null, null, []);
        $banner3 = new Banner('3', '3', 1, null, null, null, null, []);
        $banner4 = new Banner('4', '4', 2, null, null, null, null, []);
        $position = new Position(
            '1234',
            'homepage.top',
            'Homepage top',
            0,
            Position::DisplayTypeMultiple,
            Position::BreakpointTypeMin,
            Position::ModeManaged,
            null,
            [],
            [$banner1, $banner2, $banner3, $banner4],
        );
        $resolver = new BannersResolver(
            $this->createClosingManager(
                'homepage.top',
                false,
                [
                    '1' => false,
                    '2' => false,
                    '3' => false,
                    '4' => false,
                ],
                2,
            ),
        );

        Assert::same([
            $banner2,
            $banner4,
            $banner3,
            $banner1,
        ], $resolver->resolveMultiple($position, 2));
    }

    public function testSortedBannersShouldBeReturnedWhenResolvingMultiplePositionWithSomeClosedBanners(): void
    {
        $banner1 = new Banner('1', '1', 0, null, null, null, null, []);
        $banner2 = new Banner('2', '2', 2, null, null, null, null, []);
        $banner3 = new Banner('3', '3', 1, null, null, null, null, []);
        $banner4 = new Banner('4', '4', 2, null, null, null, null, []);
        $position = new Position(
            '1234',
            'homepage.top',
            'Homepage top',
            0,
            Position::DisplayTypeMultiple,
            Position::BreakpointTypeMin,
            Position::ModeManaged,
            null,
            [],
            [$banner1, $banner2, $banner3, $banner4],
        );
        $resolver = new BannersResolver(
            $this->createClosingManager(
                'homepage.top',
                false,
                [
                    '1' => false,
                    '2' => true,
                    '3' => true,
                    '4' => false,
                ],
                2,
            ),
        );

        Assert::same([
            $banner4,
            $banner1,
        ], $resolver->resolveMultiple($position, 2));
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
            null,
            [],
            [],
        );
        $resolver = new BannersResolver(
            $this->createClosingManager(
                'homepage.top',
                false,
                [],
                2,
            ),
        );

        Assert::null($resolver->resolveRandom($position, 2));
    }

    public function testNullShouldBeReturnedWhenResolvingClosedRandomPosition(): void
    {
        $position = new Position(
            '1234',
            'homepage.top',
            'Homepage top',
            0,
            Position::DisplayTypeRandom,
            Position::BreakpointTypeMin,
            Position::ModeManaged,
            null,
            [],
            [
                new Banner('1', '1', 1, null, null, null, null, []),
            ],
        );
        $resolver = new BannersResolver(
            $this->createClosingManager(
                'homepage.top',
                true,
                [],
                2,
            ),
        );

        Assert::null($resolver->resolveRandom($position, 2));
    }

    public function testRandomBannerShouldBeReturnedWhenResolvingRandomPosition(): void
    {
        $banner1 = new Banner('1', '1', 1, null, null, null, null, []);
        $banner2 = new Banner('2', '2', 3, null, null, null, null, []);
        $banner3 = new Banner('3', '3', 2, null, null, null, null, []);
        $banner4 = new Banner('4', '4', 3, null, null, null, null, []);
        $position = new Position(
            '1234',
            'homepage.top',
            'Homepage top',
            0,
            Position::DisplayTypeRandom,
            Position::BreakpointTypeMin,
            Position::ModeManaged,
            null,
            [],
            [$banner1, $banner2, $banner3, $banner4],
        );
        $resolver = new BannersResolver(
            $this->createClosingManager(
                'homepage.top',
                false,
                [
                    '1' => false,
                    '2' => false,
                    '3' => false,
                    '4' => false,
                ],
                3,
            ),
        );

        Assert::type(Banner::class, $resolver->resolveRandom($position, 3)); # @todo: Mock mt_rand() ?
    }

    public function testRandomBannerShouldBeReturnedWhenResolvingRandomPositionWithSomeClosedBanners(): void
    {
        $banner1 = new Banner('1', '1', 1, null, null, null, null, []);
        $banner2 = new Banner('2', '2', 3, null, null, null, null, []);
        $banner3 = new Banner('3', '3', 2, null, null, null, null, []);
        $banner4 = new Banner('4', '4', 3, null, null, null, null, []);
        $position = new Position(
            '1234',
            'homepage.top',
            'Homepage top',
            0,
            Position::DisplayTypeRandom,
            Position::BreakpointTypeMin,
            Position::ModeManaged,
            null,
            [],
            [$banner1, $banner2, $banner3, $banner4],
        );
        $resolver = new BannersResolver(
            $this->createClosingManager(
                'homepage.top',
                false,
                [
                    '1' => true,
                    '2' => true,
                    '3' => false,
                    '4' => true,
                ],
                3,
            ),
        );

        Assert::same($banner3, $resolver->resolveRandom($position, 3));
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    /**
     * @param array<string, bool> $banners
     */
    private function createClosingManager(string $positionCode, bool $positionClosed, array $banners, int $revision): ClosingManagerInterface
    {
        $mock = Mockery::mock(ClosingManagerInterface::class);

        $mock->shouldReceive('isPositionClosed')
            ->once()
            ->with($positionCode, $revision)
            ->andReturn($positionClosed);

        foreach ($banners as $bannerId => $closed) {
            $mock->shouldReceive('isBannerClosed')
                ->once()
                ->with($positionCode, $bannerId, $revision)
                ->andReturn($closed);
        }

        return $mock;
    }
}

(new BannersResolverTest())->run();
