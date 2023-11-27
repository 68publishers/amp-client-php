<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Bridge\Latte\Event;

use SixtyEightPublishers\AmpClient\AmpClientInterface;

final class ConfigureClientEvent
{
    private AmpClientInterface $client;

    public function __construct(
        AmpClientInterface $client
    ) {
        $this->client = $client;
    }

    public function getClient(): AmpClientInterface
    {
        return $this->client;
    }

    public function withClient(AmpClientInterface $client): self
    {
        return new self($client);
    }
}
