<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Bridge\Nette\Application;

use Nette\Application\AbortException;
use Nette\Application\IResponse;
use Nette\Application\Responses\JsonResponse;
use Nette\Application\Responses\TextResponse;
use Nette\Application\UI\ITemplate;
use Nette\Application\UI\Presenter;
use SixtyEightPublishers\AmpClient\Bridge\Latte\RendererProvider;
use SixtyEightPublishers\AmpClient\Exception\AmpExceptionInterface;
use SixtyEightPublishers\AmpClient\Renderer\OutputBuffer;
use Throwable;
use function count;
use function is_array;

final class RenderQueuedPositionsOnPresenterShutdownHandler
{
    private RendererProvider $rendererProvider;

    public function __construct(RendererProvider $rendererProvider)
    {
        $this->rendererProvider = $rendererProvider;
    }

    public static function attach(Presenter $presenter, RendererProvider $rendererProvider): void
    {
        $presenter->onShutdown[] = new self($rendererProvider);
    }

    /**
     * @throws AmpExceptionInterface
     * @throws Throwable
     */
    public function __invoke(Presenter $presenter, IResponse $response): void
    {
        if (!$this->rendererProvider->supportsQueues()) {
            return;
        }

        try {
            if ($response instanceof TextResponse) {
                $this->processTextResponse($presenter, $response);
            } elseif ($response instanceof JsonResponse) {
                $this->processJsonResponse($presenter, $response);
            }
        } catch (AbortException $e) {
        }
    }

    /**
     * @throws AmpExceptionInterface
     * @throws AbortException
     * @throws Throwable
     */
    private function processTextResponse(Presenter $presenter, TextResponse $response): void
    {
        $source = $response->getSource();

        if ($source instanceof ITemplate) {
            $output = OutputBuffer::capture(static function () use ($source): void {
                $source->render();
            });
        } else {
            $output = (string) $source;
        }

        if ($this->rendererProvider->isAnythingQueued()) {
            $output = $this->rendererProvider->renderQueuedPositions($output);
        }

        $presenter->sendResponse(new TextResponse($output));
    }

    /**
     * @throws AmpExceptionInterface
     * @throws AbortException
     */
    private function processJsonResponse(Presenter $presenter, JsonResponse $response): void
    {
        if (!$this->rendererProvider->isAnythingQueued()) {
            return;
        }

        $payload = $response->getPayload();
        $snippets = $payload->snippets ?? [];

        if (is_array($snippets) && 0 < count($snippets)) {
            $payload->snippets = $this->rendererProvider->renderQueuedPositions($snippets);

            $presenter->sendResponse(new JsonResponse($payload, $response->getContentType()));
        }
    }
}
