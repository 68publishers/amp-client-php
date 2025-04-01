<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Closing;

interface ClosedEntriesStoreInterface
{
    public function isClosed(EntryKey $key, int $revision): bool;
}
