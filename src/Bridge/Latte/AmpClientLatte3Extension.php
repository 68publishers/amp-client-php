<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Bridge\Latte;

use Latte\Extension;
use SixtyEightPublishers\AmpClient\Bridge\Latte\Node\BannerNode;

final class AmpClientLatte3Extension extends Extension
{
    private RendererProvider $rendererProvider;

    private string $tagName;

    public function __construct(RendererProvider $rendererProvider, string $tagName = 'banner')
    {
        $this->rendererProvider = $rendererProvider;
        $this->tagName = $tagName;
    }

    /**
     * @return array{
     *     ampClientRenderer: RendererProvider,
     * }
     */
    public function getProviders(): array
    {
        return [
            'ampClientRenderer' => $this->rendererProvider,
        ];
    }

    /**
     * @return array<string, callable>
     */
    public function getTags(): array
    {
        return [
            $this->tagName => [BannerNode::class, 'create'],
        ];
    }
}
