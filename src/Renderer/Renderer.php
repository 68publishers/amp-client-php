<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Renderer;

use SixtyEightPublishers\AmpClient\Exception\RendererException;
use SixtyEightPublishers\AmpClient\Renderer\Phtml\PhtmlRendererBridge;
use SixtyEightPublishers\AmpClient\Request\ValueObject\Position as RequestPosition;
use SixtyEightPublishers\AmpClient\Response\ValueObject\Position as ResponsePosition;
use Throwable;
use function get_class;

final class Renderer implements RendererInterface
{
    private BannersResolverInterface $bannersResolver;

    private RendererBridgeInterface $rendererBridge;

    public function __construct(
        BannersResolverInterface $bannersResolver,
        RendererBridgeInterface $rendererBridge
    ) {
        $this->bannersResolver = $bannersResolver;
        $this->rendererBridge = $rendererBridge;
    }

    public static function create(?RendererBridgeInterface $rendererBridge = null): self
    {
        return new self(
            new BannersResolver(),
            $rendererBridge ?? new PhtmlRendererBridge(),
        );
    }

    public function render(ResponsePosition $position, array $elementAttributes = [], array $options = []): string
    {
        try {
            switch ($position->getDisplayType()) {
                case null:
                    return $this->rendererBridge->renderNotFound(
                        $position,
                        $elementAttributes,
                        $options,
                    );
                case ResponsePosition::DisplayTypeMultiple:
                    return $this->rendererBridge->renderMultiple(
                        $position,
                        $this->bannersResolver->resolveMultiple($position),
                        $elementAttributes,
                        $options,
                    );
                case ResponsePosition::DisplayTypeRandom:
                    return $this->rendererBridge->renderRandom(
                        $position,
                        $this->bannersResolver->resolveRandom($position),
                        $elementAttributes,
                        $options,
                    );
                case ResponsePosition::DisplayTypeSingle:
                default:
                    return $this->rendererBridge->renderSingle(
                        $position,
                        $this->bannersResolver->resolveSingle($position),
                        $elementAttributes,
                        $options,
                    );
            }
        } catch (Throwable $e) {
            if ($e instanceof RendererException) {
                throw $e;
            }

            throw RendererException::rendererBridgeThrownError(
                get_class($this->rendererBridge),
                $position->getCode(),
                $e,
            );
        }
    }

    public function renderClientSide(RequestPosition $position, array $elementAttributes = [], array $options = [], ?ClientSideMode $mode = null): string
    {
        $mode = $mode ?? ClientSideMode::managed();

        if ($mode->isEmbed()) {
            $options['omit-default-resources'] = '1';
        }

        try {
            return $this->rendererBridge->renderClientSide(
                $position,
                $mode,
                $elementAttributes,
                $options,
            );
        } catch (Throwable $e) {
            if ($e instanceof RendererException) {
                throw $e;
            }

            throw RendererException::rendererBridgeThrownError(
                get_class($this->rendererBridge),
                $position->getCode(),
                $e,
            );
        }
    }
}
