<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Bridge\Latte;

use Psr\Log\LoggerInterface;
use SixtyEightPublishers\AmpClient\AmpClientInterface;
use SixtyEightPublishers\AmpClient\Bridge\Latte\Event\ConfigureClientEvent;
use SixtyEightPublishers\AmpClient\Bridge\Latte\Event\ConfigureClientEventHandlerInterface;
use SixtyEightPublishers\AmpClient\Bridge\Latte\RenderingMode\DirectRenderingMode;
use SixtyEightPublishers\AmpClient\Bridge\Latte\RenderingMode\RenderingModeInterface;
use SixtyEightPublishers\AmpClient\Exception\AmpExceptionInterface;
use SixtyEightPublishers\AmpClient\Exception\RendererException;
use SixtyEightPublishers\AmpClient\Renderer\RendererInterface;
use SixtyEightPublishers\AmpClient\Request\BannersRequest;
use SixtyEightPublishers\AmpClient\Request\ValueObject\BannerResource;
use SixtyEightPublishers\AmpClient\Request\ValueObject\Position as RequestPosition;
use SixtyEightPublishers\AmpClient\Response\BannersResponse;
use SixtyEightPublishers\AmpClient\Response\ValueObject\Position as ResponsePosition;
use function array_filter;
use function array_keys;
use function array_values;
use function assert;
use function count;
use function htmlspecialchars;
use function is_array;
use function sprintf;
use function str_replace;

final class RendererProvider
{
    private const OptionResources = 'resources';

    private AmpClientInterface $client;

    private RendererInterface $renderer;

    private ?LoggerInterface $logger;

    private RenderingModeInterface $renderingMode;

    private bool $debugMode = false;

    /** @var array<string, RequestPosition> */
    private array $queue = [];

    /** @var array<class-string, array<int, object>> */
    private array $eventHandlers = [
        ConfigureClientEventHandlerInterface::class => [],
    ];

    private bool $clientConfigured = false;

    public function __construct(
        AmpClientInterface $client,
        RendererInterface $renderer,
        ?LoggerInterface $logger = null
    ) {
        $this->client = $client;
        $this->renderer = $renderer;
        $this->logger = $logger;
        $this->renderingMode = new DirectRenderingMode();
    }

    /**
     * @param array<string, mixed> $options
     *
     * @throws AmpExceptionInterface
     */
    public function __invoke(object $globals, string $positionCode, array $options = []): string
    {
        $position = $this->createPosition($positionCode, $options);

        if ($this->renderingMode->shouldBePositionQueued($position, $globals)) {
            return $this->addToQueue($position);
        } else {
            return $this->render($position);
        }
    }

    public function setDebugMode(bool $debugMode): self
    {
        $this->debugMode = $debugMode;

        return $this;
    }

    public function setRenderingMode(RenderingModeInterface $renderingMode): self
    {
        $this->renderingMode = $renderingMode;

        return $this;
    }

    public function addConfigureClientEventHandler(ConfigureClientEventHandlerInterface $handler): self
    {
        $this->eventHandlers[ConfigureClientEventHandlerInterface::class][] = $handler;

        return $this;
    }

    /**
     * @throws AmpExceptionInterface
     */
    private function render(RequestPosition $position): string
    {
        $positionCode = $position->getCode();
        $response = $this->fetchResponse(new BannersRequest([$position]));

        if (null === $response || null === $response->getPosition($positionCode)) {
            return '';
        }

        return $this->renderPosition($response->getPosition($positionCode));
    }

    private function addToQueue(RequestPosition $position): string
    {
        $comment = $this->formatHtmlComment($position->getCode());
        $this->queue[$comment] = $position;

        return $comment;
    }

    public function supportsQueues(): bool
    {
        return $this->renderingMode->supportsQueues();
    }

    public function isAnythingQueued(): bool
    {
        return 0 < count($this->queue);
    }

    /**
     * @param string|array<string> $output
     *
     * @return string|array<string>
     * @phpstan-return ($output is array<string> ? array<string> : string)
     *
     * @throws AmpExceptionInterface
     */
    public function renderQueuedPositions($output)
    {
        if (0 >= count($this->queue)) {
            return $output;
        }

        $response = $this->fetchResponse(
            new BannersRequest(array_values($this->queue)),
        );

        if (null === $response) {
            $this->queue = [];

            return $output;
        }

        $replacements = array_filter(
            array_map(
                function (RequestPosition $requestPosition) use ($response): ?string {
                    $responsePosition = $response->getPosition($requestPosition->getCode());

                    if (null === $responsePosition) {
                        return null;
                    }

                    return $this->renderPosition($responsePosition);
                },
                $this->queue,
            ),
            static fn (?string $html): bool => null !== $html && '' !== $html,
        );

        if (0 >= count($replacements)) {
            $this->queue = [];

            return $output;
        }

        $search = array_keys($replacements);
        $replace = array_values($replacements);

        if (is_array($output)) {
            foreach ($output as $k => $v) {
                $output[$k] = str_replace($search, $replace, $v);
            }
        } else {
            $output = str_replace($search, $replace, $output);
        }

        $this->queue = [];

        return $output;
    }

    /**
     * @throws AmpExceptionInterface
     */
    private function fetchResponse(BannersRequest $request): ?BannersResponse
    {
        if (!$this->clientConfigured && 0 < count($this->eventHandlers[ConfigureClientEventHandlerInterface::class])) {
            $event = new ConfigureClientEvent($this->client);

            foreach ($this->eventHandlers[ConfigureClientEventHandlerInterface::class] as $eventHandler) {
                assert($eventHandler instanceof ConfigureClientEventHandlerInterface);
                $event = $eventHandler($event);
            }

            $this->client = $event->getClient();
            $this->clientConfigured = true;
        }

        try {
            return $this->client->fetchBanners($request);
        } catch (AmpExceptionInterface $e) {
            if ($this->debugMode) {
                throw $e;
            }

            if (null !== $this->logger) {
                $this->logger->error($e->getMessage(), [
                    'exception' => $e,
                ]);
            }

            return null;
        }
    }

    /**
     * @throws RendererException
     */
    private function renderPosition(ResponsePosition $position): string
    {
        try {
            return $this->renderer->render($position);
        } catch (RendererException $e) {
            if ($this->debugMode) {
                throw $e;
            }

            if (null !== $this->logger) {
                $this->logger->error($e->getMessage(), [
                    'exception' => $e,
                ]);
            }

            return '';
        }
    }

    /**
     * @param array<string, mixed> $options
     */
    private function createPosition(string $positionCode, array $options): RequestPosition
    {
        $resources = (array) ($options[self::OptionResources] ?? []);
        $bannerResources = [];

        foreach ($resources as $resourceCode => $resourceValues) {
            $bannerResources[] = $resourceValues instanceof BannerResource ? $resourceValues : new BannerResource($resourceCode, $resourceValues);
        }

        return new RequestPosition($positionCode, $bannerResources);
    }

    private function formatHtmlComment(string $positionCode): string
    {
        return sprintf(
            '<!--AMP_POSITION:%s-->',
            htmlspecialchars($positionCode),
        );
    }
}
