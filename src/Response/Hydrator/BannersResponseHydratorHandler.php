<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Response\Hydrator;

use SixtyEightPublishers\AmpClient\Response\BannersResponse;
use SixtyEightPublishers\AmpClient\Response\ValueObject\Banner;
use SixtyEightPublishers\AmpClient\Response\ValueObject\ContentInterface;
use SixtyEightPublishers\AmpClient\Response\ValueObject\Dimensions;
use SixtyEightPublishers\AmpClient\Response\ValueObject\HtmlContent;
use SixtyEightPublishers\AmpClient\Response\ValueObject\ImageContent;
use SixtyEightPublishers\AmpClient\Response\ValueObject\Position;
use SixtyEightPublishers\AmpClient\Response\ValueObject\Source;
use function array_map;

/**
 * @phpstan-type HtmlContentData = array{
 *     breakpoint: int|null,
 *     type: string,
 *     html: string,
 * }
 *
 * @phpstan-type ImageContentData = array{
 *      breakpoint: int|null,
 *      type: string,
 *      href: string,
 *      target: string|null,
 *      alt: string,
 *      title: string,
 *      src: string,
 *      srcset: string,
 *      sources: array<int, array{
 *          type: string,
 *          srcset: string,
 *      }>,
 *      sizes: string,
 *  }
 *
 * @phpstan-type BannerData = array{
 *     id: string,
 *     name: string,
 *     score: int|float,
 *     campaign_id: string|null,
 *     campaign_code: string|null,
 *     campaign_name: string|null,
 *     contents: array<int, HtmlContentData|ImageContentData>,
 * }
 *
 * @phpstan-type DimensionsData = array{
 *     width: int|null,
 *     height: int|null,
 * }
 *
 * @phpstan-type PositionData = array{
 *     position_id?: string|null,
 *     position_name?: string|null,
 *     rotation_seconds: int,
 *     display_type: string|null,
 *     breakpoint_type: string,
 *     mode?: string,
 *     dimensions?: DimensionsData,
 *     banners: array<int, BannerData>,
 * }
 *
 * @phpstan-type BannersResponseBody = array{
 *     status: string,
 *     data: array<string, PositionData>,
 * }
 */
final class BannersResponseHydratorHandler implements ResponseHydratorHandlerInterface
{
    public function canHydrateResponse(string $responseClassname): bool
    {
        return $responseClassname === BannersResponse::class;
    }

    /**
     * @param BannersResponseBody $responseBody
     */
    public function hydrate($responseBody): BannersResponse
    {
        $data = $responseBody['data'];
        $positions = [];

        foreach ($data as $positionCode => $positionData) {
            $positions[$positionCode] = new Position(
                $positionData['position_id'] ?? null,
                $positionCode,
                $positionData['position_name'] ?? null,
                $positionData['rotation_seconds'],
                $positionData['display_type'] ?? null,
                $positionData['breakpoint_type'],
                $positionData['mode'] ?? Position::ModeManaged,
                $positionData['options'] ?? [],
                $this->hydrateDimensions($positionData['dimensions'] ?? null),
                $this->hydrateBanners($positionData['banners']),
            );
        }

        return new BannersResponse($positions);
    }

    /**
     * @param array<int, BannerData> $bannersData
     *
     * @return array<int, Banner>
     */
    private function hydrateBanners(array $bannersData): array
    {
        $banners = [];

        foreach ($bannersData as $bannerData) {
            $banners[] = new Banner(
                $bannerData['id'],
                $bannerData['name'],
                $bannerData['score'],
                $bannerData['campaign_id'],
                $bannerData['campaign_code'],
                $bannerData['campaign_name'],
                $this->hydrateContents($bannerData['contents']),
            );
        }

        return $banners;
    }

    /**
     * @param array<int, HtmlContentData|ImageContentData> $contentsData
     *
     * @return array<int, ContentInterface>
     */
    private function hydrateContents(array $contentsData): array
    {
        $contents = [];

        foreach ($contentsData as $contentData) {
            switch ($contentData['type']) {
                case ContentInterface::TypeImage:
                    /** @var ImageContentData $contentData */
                    $contents[] = new ImageContent(
                        $contentData['breakpoint'],
                        $contentData['href'],
                        $contentData['target'],
                        $contentData['alt'],
                        $contentData['title'],
                        $contentData['src'],
                        $contentData['srcset'],
                        $contentData['sizes'],
                        array_map(
                            static fn (array $sourceData): Source => new Source(
                                $sourceData['type'],
                                $sourceData['srcset'],
                            ),
                            $contentData['sources'],
                        ),
                    );

                    break;
                case ContentInterface::TypeHtml:
                    /** @var HtmlContentData $contentData */
                    $contents[] = new HtmlContent(
                        $contentData['breakpoint'],
                        $contentData['html'],
                    );

                    break;
            }
        }

        return $contents;
    }

    /**
     * @param DimensionsData|null $dimensions
     */
    private function hydrateDimensions(?array $dimensions): Dimensions
    {
        if (null === $dimensions) {
            return new Dimensions(null, null);
        }

        return new Dimensions(
            $dimensions['width'] ?? null,
            $dimensions['height'] ?? null,
        );
    }
}
