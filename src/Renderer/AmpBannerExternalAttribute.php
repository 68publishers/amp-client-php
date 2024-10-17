<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Renderer;

use JsonException;
use SixtyEightPublishers\AmpClient\Exception\RendererException;
use SixtyEightPublishers\AmpClient\Response\ValueObject\Position;

final class AmpBannerExternalAttribute
{
    private Position $position;

    private string $state;

    private string $stateInfo;

    private function __construct(Position $position, string $state, string $stateInfo)
    {
        $this->position = $position;
        $this->state = $state;
        $this->stateInfo = $stateInfo;
    }

    public static function rendered(Position $position): self
    {
        return new self($position, 'RENDERED', 'Banner was successfully rendered server-side.');
    }

    public static function notFound(Position $position): self
    {
        return new self($position, 'NOT_FOUND', 'Banner not found in fetched response on the server.');
    }

    public function __toString(): string
    {
        $components = [
            'positionData' => [
                'id' => $this->position->getId(),
                'code' => $this->position->getCode(),
                'name' => $this->position->getName(),
                'rotationSeconds' => $this->position->getRotationSeconds(),
                'displayType' => $this->position->getDisplayType(),
                'breakpointType' => $this->position->getBreakpointType(),
                'closeExpiration' => $this->position->getCloseExpiration(),
            ],
            'state' => [
                'value' => $this->state,
                'info' => $this->stateInfo,
            ],
        ];

        try {
            $json = json_encode($components, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        } catch (JsonException $e) {
            throw RendererException::unableToRenderAmpBannerExternalAttribute($this->position->getCode(), $e);
        }

        return base64_encode(
            rawurlencode($json),
        );
    }
}
