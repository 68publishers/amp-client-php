<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Renderer;

use SixtyEightPublishers\AmpClient\Closing\ClosingManagerInterface;
use SixtyEightPublishers\AmpClient\Response\ValueObject\Banner;
use SixtyEightPublishers\AmpClient\Response\ValueObject\Position;
use function array_filter;
use function array_map;
use function array_search;
use function array_values;
use function count;
use function max;
use function mt_getrandmax;
use function mt_rand;
use function usort;

class BannersResolver implements BannersResolverInterface
{
    private ClosingManagerInterface $closingManager;

    public function __construct(
        ClosingManagerInterface $closingManager
    ) {
        $this->closingManager = $closingManager;
    }

    public function resolveSingle(Position $position, int $closedRevision = 0): ?Banner
    {
        if ($this->closingManager->isPositionClosed($position->getCode(), $closedRevision)) {
            return null;
        }

        $banners = array_values(
            array_filter(
                $position->getBanners(),
                fn (Banner $banner): bool => !$this->closingManager->isBannerClosed($position->getCode(), $banner->getId(), $closedRevision),
            ),
        );

        if (0 >= count($banners)) {
            return null;
        }

        $scores = array_map(
            static fn (Banner $banner) => $banner->getScore(),
            $banners,
        );
        $firstHighestScoreKey = array_search(max($scores), $scores, true);

        return $banners[$firstHighestScoreKey] ?? null;
    }

    public function resolveRandom(Position $position, int $closedRevision = 0): ?Banner
    {
        if ($this->closingManager->isPositionClosed($position->getCode(), $closedRevision)) {
            return null;
        }

        $banners = array_values(
            array_filter(
                $position->getBanners(),
                fn (Banner $banner): bool => !$this->closingManager->isBannerClosed($position->getCode(), $banner->getId(), $closedRevision),
            ),
        );

        if (0 >= count($banners)) {
            return null;
        }

        $distributions = [];
        $weightTotal = 0;

        foreach ($banners as $banner) {
            $weightTotal += $banner->getScore();
        }

        foreach ($banners as $index => $banner) {
            $distributions[$index] = $banner->getScore() / $weightTotal;
        }

        $key = 0;
        $selector = mt_rand() / mt_getrandmax();

        while (0 < $selector) {
            $selector -= $distributions[$key];
            $key++;
        }

        $key--;

        return $banners[$key] ?? $banners[0];
    }

    public function resolveMultiple(Position $position, int $closedRevision = 0): array
    {
        if ($this->closingManager->isPositionClosed($position->getCode(), $closedRevision)) {
            return [];
        }

        $banners = array_values(
            array_filter(
                $position->getBanners(),
                fn (Banner $banner): bool => !$this->closingManager->isBannerClosed($position->getCode(), $banner->getId(), $closedRevision),
            ),
        );

        if (0 >= count($banners)) {
            return [];
        }

        usort(
            $banners,
            static fn (Banner $left, Banner $right): int => $right->getScore() <=> $left->getScore(),
        );

        return $banners;
    }
}
