<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Closing;

final class ClosingManager implements ClosingManagerInterface
{
    private ClosedEntriesStoreInterface $store;

    public function __construct(
        ClosedEntriesStoreInterface $store
    ) {
        $this->store = $store;
    }

    public function isBannerClosed(string $positionCode, string $bannerId, int $revision): bool
    {
        return $this->store->isClosed(
            EntryKey::banner(
                $positionCode,
                $bannerId,
            ),
            $revision,
        );
    }

    public function isPositionClosed(string $positionCode, int $revision): bool
    {
        return $this->store->isClosed(
            EntryKey::position(
                $positionCode,
            ),
            $revision,
        );
    }
}
