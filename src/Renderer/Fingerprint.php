<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Renderer;

use JsonException;
use SixtyEightPublishers\AmpClient\Exception\RendererException;
use SixtyEightPublishers\AmpClient\Response\ValueObject\Banner;
use SixtyEightPublishers\AmpClient\Response\ValueObject\Position;
use function base64_encode;
use function json_encode;
use function rawurlencode;

final class Fingerprint
{
    private string $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    /**
     * @throws RendererException
     */
    public static function create(Position $position, Banner $banner): self
    {
        $components = [
            'bannerId' => $banner->getId(),
            'bannerName' => $banner->getName(),
            'positionId' => $position->getId(),
            'positionCode' => $position->getCode(),
            'positionName' => $position->getName(),
            'campaignId' => $banner->getCampaignId(),
            'campaignCode' => $banner->getCampaignCode(),
            'campaignName' => $banner->getCampaignName(),
            'closeExpiration' => $banner->getCloseExpiration(),
        ];

        try {
            $json = json_encode($components, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        } catch (JsonException $e) {
            throw RendererException::unableToCreateFingerprint($position->getCode(), $banner->getId(), $e);
        }

        return new self(
            base64_encode(
                rawurlencode($json),
            ),
        );
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->getValue();
    }
}
