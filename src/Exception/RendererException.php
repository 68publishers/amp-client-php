<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Exception;

use RuntimeException;
use Throwable;
use function sprintf;

final class RendererException extends RuntimeException implements AmpExceptionInterface
{
    public static function unableToCreateFingerprint(string $positionCode, string $bannerId, ?Throwable $previous = null): self
    {
        return new self(
            sprintf(
                'Unable to create fingerprint for banner %s on the position %s. %s',
                $bannerId,
                $positionCode,
                null !== $previous ? $previous->getMessage() : '',
            ),
            0,
            $previous,
        );
    }

    public static function unableToRenderAmpBannerExternalAttribute(string $positionCode, ?Throwable $previous = null): self
    {
        return new self(
            sprintf(
                'Unable to render amp-banner-external for the position %s. %s',
                $positionCode,
                null !== $previous ? $previous->getMessage() : '',
            ),
            0,
            $previous,
        );
    }

    /**
     * @param class-string $rendererBridgeClassname
     */
    public static function rendererBridgeThrownError(string $rendererBridgeClassname, string $positionCode, Throwable $previous): self
    {
        return new self(
            sprintf(
                'Renderer bridge of type %s thrown an exception while rendering a position %s: %s',
                $rendererBridgeClassname,
                $positionCode,
                $previous->getMessage(),
            ),
            0,
            $previous,
        );
    }

    public static function templateFileNotDefined(string $type): self
    {
        return new self(
            sprintf(
                'Template file of type "%s" not defined.',
                $type,
            ),
        );
    }

    public static function templateFileNotFound(string $filename): self
    {
        return new self(
            sprintf(
                'Template file "%s" not found.',
                $filename,
            ),
        );
    }
}
