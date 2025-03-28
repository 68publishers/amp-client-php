<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Closing;

final class NullClosedEntriesStore implements ClosedEntriesStoreInterface
{
    public function isClosed(EntryKey $key): bool
    {
        return false;
    }
}
