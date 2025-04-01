<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Renderer;

use Closure;
use SixtyEightPublishers\AmpClient\Closing\ClosingManager;
use SixtyEightPublishers\AmpClient\Closing\ClosingManagerInterface;
use SixtyEightPublishers\AmpClient\Closing\NullClosedEntriesStore;
use SixtyEightPublishers\AmpClient\Exception\RendererException;
use SixtyEightPublishers\AmpClient\Expression\ExpressionParser;
use SixtyEightPublishers\AmpClient\Expression\ExpressionParserInterface;
use SixtyEightPublishers\AmpClient\Renderer\Phtml\PhtmlRendererBridge;
use SixtyEightPublishers\AmpClient\Request\ValueObject\Position as RequestPosition;
use SixtyEightPublishers\AmpClient\Response\ValueObject\Banner;
use SixtyEightPublishers\AmpClient\Response\ValueObject\Position as ResponsePosition;
use SixtyEightPublishers\AmpClient\Response\ValueObject\Settings;
use Throwable;
use function explode;
use function get_class;
use function preg_match;

final class Renderer implements RendererInterface
{
    private BannersResolverInterface $bannersResolver;

    private RendererBridgeInterface $rendererBridge;

    private ExpressionParserInterface $expressionParser;

    public function __construct(
        BannersResolverInterface $bannersResolver,
        RendererBridgeInterface $rendererBridge,
        ExpressionParserInterface $expressionParser
    ) {
        $this->bannersResolver = $bannersResolver;
        $this->rendererBridge = $rendererBridge;
        $this->expressionParser = $expressionParser;
    }

    public static function create(
        ?RendererBridgeInterface $rendererBridge = null,
        ?ClosingManagerInterface $closingManager = null
    ): self {
        return new self(
            new BannersResolver(
                $closingManager ?? new ClosingManager(
                    new NullClosedEntriesStore(),
                ),
            ),
            $rendererBridge ?? new PhtmlRendererBridge(),
            new ExpressionParser(),
        );
    }

    public function render(ResponsePosition $position, Settings $settings, array $elementAttributes = [], array $options = []): string
    {
        try {
            $options = new Options($options, $this->expressionParser);
            $options->override($position->getOptions());
            $displayType = $position->getDisplayType();

            if (null === $displayType || [] === $position->getBanners()) {
                return $this->rendererBridge->renderNotFound(
                    $position,
                    $this->resolveElementAttributes($elementAttributes, null),
                    $options,
                );
            }

            switch ($position->getDisplayType()) {
                case ResponsePosition::DisplayTypeMultiple:
                    $banners = $this->bannersResolver->resolveMultiple($position, $settings->getCloseRevision());

                    return [] !== $banners
                        ? $this->rendererBridge->renderMultiple(
                            $position,
                            $banners,
                            $this->resolveElementAttributes($elementAttributes, $banners),
                            $options,
                        )
                        : $this->rendererBridge->renderClosed(
                            $position,
                            $this->resolveElementAttributes($elementAttributes, null),
                            $options,
                        );
                case ResponsePosition::DisplayTypeRandom:
                    $banner = $this->bannersResolver->resolveRandom($position, $settings->getCloseRevision());

                    return null !== $banner
                        ? $this->rendererBridge->renderRandom(
                            $position,
                            $banner,
                            $this->resolveElementAttributes($elementAttributes, $banner),
                            $options,
                        )
                        : $this->rendererBridge->renderClosed(
                            $position,
                            $this->resolveElementAttributes($elementAttributes, null),
                            $options,
                        );
                case ResponsePosition::DisplayTypeSingle:
                default:
                    $banner = $this->bannersResolver->resolveSingle($position, $settings->getCloseRevision());

                    return null !== $banner
                        ? $this->rendererBridge->renderSingle(
                            $position,
                            $banner,
                            $this->resolveElementAttributes($elementAttributes, $banner),
                            $options,
                        )
                        : $this->rendererBridge->renderClosed(
                            $position,
                            $this->resolveElementAttributes($elementAttributes, null),
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
                $this->resolveElementAttributes($elementAttributes, null),
                new Options($options, $this->expressionParser),
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

    /**
     * @param array<string, mixed>     $elementAttributes
     * @param Banner|list<Banner>|null $banner
     *
     * @return array<string, mixed>
     */
    private function resolveElementAttributes(array $elementAttributes, $banner): array
    {
        /**
         * @param Closure(Banner $banner): bool $filter
         *
         * @return void
         */
        $resolve = static function (Closure $filter) use ($banner): bool {
            if (null === $banner) {
                return false;
            }

            if ($banner instanceof Banner) {
                return $filter($banner);
            }

            foreach ($banner as $b) {
                if ($filter($b)) {
                    return true;
                }
            }

            return false;
        };

        $attributesMap = [];

        foreach ($elementAttributes as $attrName => $attrValue) {
            [$cond, $attrName] = explode('@', $attrName, 2) +  ['', ''];

            if ('' === $attrName) {
                $attributesMap[] = [$cond, $attrValue];

                continue;
            }

            switch (true) {
                case 'exists' === $cond:
                    if ($resolve(static fn (Banner $banner): bool => [] !== $banner->getContents())) {
                        $attributesMap[] = [$attrName, $attrValue];
                    }

                    break;
                case preg_match('/^exists\((?P<BP>default|\d+)\)$/', $cond, $matches):
                    $bp = 'default' === $matches['BP'] ? null : (int) $matches['BP'];

                    if ($resolve(static function (Banner $banner) use ($bp): bool {
                        foreach ($banner->getContents() as $content) {
                            if ($content->getBreakpoint() === $bp) {
                                return true;
                            }
                        }

                        return false;
                    })) {
                        $attributesMap[] = [$attrName, $attrValue];
                    }
            }
        }

        $resolvedAttributes = [];

        foreach ($attributesMap as [$attrName, $attrValue]) {
            if ('class' === $attrName && isset($resolvedAttributes['class'])) {
                $attrValue = $resolvedAttributes['class'] . ' ' . $attrValue;
            }

            $resolvedAttributes[$attrName] = $attrValue;
        }

        return $resolvedAttributes;
    }
}
