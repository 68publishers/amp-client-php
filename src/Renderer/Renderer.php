<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Renderer;

use SixtyEightPublishers\AmpClient\Exception\RendererException;
use SixtyEightPublishers\AmpClient\Renderer\Phtml\PhtmlRendererBridge;
use SixtyEightPublishers\AmpClient\Response\ValueObject\Position;
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

    public function render(Position $position): string
    {
        try {
            switch ($position->getDisplayType()) {
                case null:
                    return $this->rendererBridge->renderNotFound($position);
                case Position::DisplayTypeMultiple:
                    return $this->rendererBridge->renderMultiple(
                        $position,
                        $this->bannersResolver->resolveMultiple($position),
                    );
                case Position::DisplayTypeRandom:
                    return $this->rendererBridge->renderRandom(
                        $position,
                        $this->bannersResolver->resolveRandom($position),
                    );
                case Position::DisplayTypeSingle:
                default:
                    return $this->rendererBridge->renderSingle(
                        $position,
                        $this->bannersResolver->resolveSingle($position),
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
}
