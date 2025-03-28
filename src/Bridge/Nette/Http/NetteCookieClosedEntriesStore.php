<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Bridge\Nette\Http;

use DateTimeImmutable;
use DateTimeZone;
use JsonException;
use Nette\Http\IRequest;
use SixtyEightPublishers\AmpClient\Closing\ClosedEntriesStoreInterface;
use SixtyEightPublishers\AmpClient\Closing\EntryKey;
use Throwable;
use function is_array;
use function is_string;
use function json_decode;
use function urldecode;

final class NetteCookieClosedEntriesStore implements ClosedEntriesStoreInterface
{
    private IRequest $request;

    private string $cookieName;

    /** @var array<string, int|false>|null */
    private ?array $closedEntries = null;

    public function __construct(
        IRequest $request,
        string $cookieName
    ) {
        $this->request = $request;
        $this->cookieName = $cookieName;
    }

    public function isClosed(EntryKey $key): bool
    {
        $closedEntries = $this->parseClosedEntries();
        $keyValue = $key->getValue();

        if (!isset($closedEntries[$keyValue])) {
            return false;
        }

        try {
            return false === $closedEntries[$keyValue]
                || $closedEntries[$keyValue] > (new DateTimeImmutable('now', new DateTimeZone('UTC')))->getTimestamp();
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * @return array<string, int|false>
     */
    private function parseClosedEntries(): array
    {
        if (null !== $this->closedEntries) {
            return $this->closedEntries;
        }

        $cookieValue = $this->request->getCookie($this->cookieName);

        if (!is_string($cookieValue)) {
            return $this->closedEntries = [];
        }

        try {
            $parsedValue = json_decode(urldecode($cookieValue), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            $parsedValue = [];
        }

        return $this->closedEntries = is_array($parsedValue) ? $parsedValue : [];
    }
}
