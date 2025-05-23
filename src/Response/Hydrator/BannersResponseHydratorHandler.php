<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Response\Hydrator;

use SixtyEightPublishers\AmpClient\Response\BannersResponse;
use SixtyEightPublishers\AmpClient\Response\ValueObject\Banner;
use SixtyEightPublishers\AmpClient\Response\ValueObject\ContentInterface;
use SixtyEightPublishers\AmpClient\Response\ValueObject\Dimensions;
use SixtyEightPublishers\AmpClient\Response\ValueObject\HtmlContent;
use SixtyEightPublishers\AmpClient\Response\ValueObject\ImageContent;
use SixtyEightPublishers\AmpClient\Response\ValueObject\NoContent;
use SixtyEightPublishers\AmpClient\Response\ValueObject\Position;
use SixtyEightPublishers\AmpClient\Response\ValueObject\Settings;
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
 *      dimensions?: DimensionsData,
 *  }
 *
 * @phpstan-type NoContentData = array{
 *     breakpoint: int|null,
 *     type: string,
 * }
 *
 * @phpstan-type BannerData = array{
 *     id: string,
 *     name: string,
 *     score: int|float,
 *     campaign_id: string|null,
 *     campaign_code: string|null,
 *     campaign_name: string|null,
 *     closed_expiration: int|null,
 *     contents: array<int, HtmlContentData|ImageContentData|NoContentData>,
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
 *     closed_expiration?: int|null,
 *     options?: array<string, string>,
 *     banners: array<int, BannerData>,
 * }
 *
 * @phpstan-type SettingsData = array{
 *     closed_revision: int,
 * }
 *
 * @phpstan-type BannersResponseBodyV1 = array{
 *     status: string,
 *     settings?: SettingsData,
 *     data: array<string, PositionData>,
 * }
 *
 * @phpstan-type BannersResponseBodyV2 = array{
 *     status: string,
 *     data: array{
 *         settings: SettingsData,
 *         positions: array<string, PositionData>,
 *     },
 * }
 */
final class BannersResponseHydratorHandler implements ResponseHydratorHandlerInterface
{
    public function canHydrateResponse(string $responseClassname): bool
    {
        return $responseClassname === BannersResponse::class;
    }

    /**
     * @param BannersResponseBodyV1|BannersResponseBodyV2 $responseBody
     */
    public function hydrate($responseBody): BannersResponse
    {
        $data = $responseBody['data'];

        if (isset($data['positions'])) { # v2
            /** @var array<string, PositionData> $positions */
            $positions = $data['positions'];
            /** @var SettingsData $settings */
            $settings = $data['settings'];
        } else { # v1
            /** @var array<string, PositionData> $positions */
            $positions = $data;
            /** @var SettingsData $settings */
            $settings = $responseBody['settings'] ?? [
                'closed_revision' => 0,
            ];
        }

        $mappedPositions = [];

        foreach ($positions as $positionCode => $positionData) {
            $mappedPositions[$positionCode] = new Position(
                $positionData['position_id'] ?? null,
                $positionCode,
                $positionData['position_name'] ?? null,
                $positionData['rotation_seconds'],
                $positionData['display_type'] ?? null,
                $positionData['breakpoint_type'],
                $positionData['mode'] ?? Position::ModeManaged,
                $positionData['closed_expiration'] ?? null,
                $positionData['options'] ?? [],
                $this->hydrateBanners($positionData['banners']),
            );
        }

        return new BannersResponse(
            $this->hydrateSettings($settings),
            $mappedPositions,
        );
    }

    /**
     * @param SettingsData $settings
     */
    private function hydrateSettings(array $settings): Settings
    {
        return new Settings(
            $settings['closed_revision'],
        );
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
                $bannerData['closed_expiration'] ?? null,
                $this->hydrateContents($bannerData['contents']),
            );
        }

        return $banners;
    }

    /**
     * @param array<int, HtmlContentData|ImageContentData|NoContentData> $contentsData
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
                        $this->hydrateDimensions($contentData['dimensions'] ?? null),
                    );

                    break;
                case ContentInterface::TypeHtml:
                    /** @var HtmlContentData $contentData */
                    $contents[] = new HtmlContent(
                        $contentData['breakpoint'],
                        $contentData['html'],
                    );

                    break;
                case ContentInterface::TypeNoContent:
                    /** @var NoContentData $contentData */
                    $contents[] = new NoContent(
                        $contentData['breakpoint'],
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
