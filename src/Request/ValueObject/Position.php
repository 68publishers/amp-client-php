<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Request\ValueObject;

use function count;
use function ksort;

final class Position
{
    private string $code;

    /** @var array<string, BannerResource> */
    private array $resources;

    /**
     * @param array<int, BannerResource> $resources
     */
    public function __construct(string $code, array $resources = [])
    {
        $this->code = $code;
        $this->resources = $this->mergeResources([], $resources);
    }

    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @return array<string, BannerResource>
     */
    public function getResources(): array
    {
        return $this->resources;
    }

    /**
     * @param array<int, BannerResource> $resources
     */
    public function withResources(array $resources): self
    {
        if (0 >= count($resources)) {
            return $this;
        }

        $position = clone $this;
        $position->resources = $this->mergeResources($this->resources, $resources);

        return $position;
    }

    /**
     * @param array<string, BannerResource> $currentResources
     * @param array<int, BannerResource>    $newResources
     *
     * @return array<string, BannerResource>
     */
    private function mergeResources(array $currentResources, array $newResources): array
    {
        foreach ($newResources as $resource) {
            $resourceCode = $resource->getCode();

            if (isset($currentResources[$resourceCode])) {
                $currentResources[$resourceCode] = $currentResources[$resourceCode]->merge($resource);
            } else {
                $currentResources[$resourceCode] = $resource;
            }
        }

        ksort($currentResources);

        return $currentResources;
    }
}
