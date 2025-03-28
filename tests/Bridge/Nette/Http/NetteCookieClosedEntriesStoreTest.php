<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Tests\Bridge\Nette\Http;

use DateTimeImmutable;
use DateTimeZone;
use Mockery;
use Nette\Http\IRequest;
use SixtyEightPublishers\AmpClient\Bridge\Nette\Http\NetteCookieClosedEntriesStore;
use SixtyEightPublishers\AmpClient\Closing\EntryKey;
use Tester\Assert;
use Tester\TestCase;
use function json_encode;
use function urlencode;

require __DIR__ . '/../../../bootstrap.php';

final class NetteCookieClosedEntriesStoreTest extends TestCase
{
    public function testShouldReturnFalseWhenNoCookieDefined(): void
    {
        $request = Mockery::mock(IRequest::class);
        $store = new NetteCookieClosedEntriesStore($request, 'amp-c');

        $request->shouldReceive('getCookie')
            ->once()
            ->with('amp-c')
            ->andReturn(null);

        Assert::false($store->isClosed(EntryKey::position('foo')));
        Assert::false($store->isClosed(EntryKey::position('bar')));
    }

    public function testShouldReturnFalseWhenNonStringCookieDefined(): void
    {
        $request = Mockery::mock(IRequest::class);
        $store = new NetteCookieClosedEntriesStore($request, 'amp-c');

        $request->shouldReceive('getCookie')
            ->once()
            ->with('amp-c')
            ->andReturn(1);

        Assert::false($store->isClosed(EntryKey::position('foo')));
        Assert::false($store->isClosed(EntryKey::position('bar')));
    }

    public function testShouldReturnFalseWhenCookieContainsInvalidJson(): void
    {
        $request = Mockery::mock(IRequest::class);
        $store = new NetteCookieClosedEntriesStore($request, 'amp-c');

        $request->shouldReceive('getCookie')
            ->once()
            ->with('amp-c')
            ->andReturn(urldecode('{"foo":'));

        Assert::false($store->isClosed(EntryKey::position('foo')));
        Assert::false($store->isClosed(EntryKey::position('bar')));
    }

    public function testShouldReturnFalseWhenCookieContainsValidNonObjectJson(): void
    {
        $request = Mockery::mock(IRequest::class);
        $store = new NetteCookieClosedEntriesStore($request, 'amp-c');

        $request->shouldReceive('getCookie')
            ->once()
            ->with('amp-c')
            ->andReturn(urldecode('"foo"'));

        Assert::false($store->isClosed(EntryKey::position('foo')));
        Assert::false($store->isClosed(EntryKey::position('bar')));
    }

    public function testShouldReturnFalseWhenKeyNotFound(): void
    {
        $request = Mockery::mock(IRequest::class);
        $store = new NetteCookieClosedEntriesStore($request, 'amp-c');

        $request->shouldReceive('getCookie')
            ->once()
            ->with('amp-c')
            ->andReturn($this->makeCookieValue([
                'p:bar' => $this->makeTimestamp('+1 second'),
                'b:bar:1' => false,
            ]));

        Assert::false($store->isClosed(EntryKey::position('foo')));
        Assert::false($store->isClosed(EntryKey::banner('foo', '1')));
    }

    public function testShouldReturnFalseWhenKeyIsExpired(): void
    {
        $request = Mockery::mock(IRequest::class);
        $store = new NetteCookieClosedEntriesStore($request, 'amp-c');

        $request->shouldReceive('getCookie')
            ->once()
            ->with('amp-c')
            ->andReturn($this->makeCookieValue([
                'p:foo' => $this->makeTimestamp('-1 second'),
                'b:foo:1' => $this->makeTimestamp('-1 second'),
            ]));

        Assert::false($store->isClosed(EntryKey::position('foo')));
        Assert::false($store->isClosed(EntryKey::banner('foo', '1')));
    }

    public function testShouldReturnTrueWhenKeyIsFalse(): void
    {
        $request = Mockery::mock(IRequest::class);
        $store = new NetteCookieClosedEntriesStore($request, 'amp-c');

        $request->shouldReceive('getCookie')
            ->once()
            ->with('amp-c')
            ->andReturn($this->makeCookieValue([
                'p:foo' => false,
                'b:foo:1' => false,
            ]));

        Assert::true($store->isClosed(EntryKey::position('foo')));
        Assert::true($store->isClosed(EntryKey::banner('foo', '1')));
    }

    public function testShouldReturnTrueWhenKeyIsExpired(): void
    {
        $request = Mockery::mock(IRequest::class);
        $store = new NetteCookieClosedEntriesStore($request, 'amp-c');

        $request->shouldReceive('getCookie')
            ->once()
            ->with('amp-c')
            ->andReturn($this->makeCookieValue([
                'p:foo' => $this->makeTimestamp('+1 second'),
                'b:foo:1' => $this->makeTimestamp('+1 second'),
            ]));

        Assert::true($store->isClosed(EntryKey::position('foo')));
        Assert::true($store->isClosed(EntryKey::banner('foo', '1')));
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    private function makeCookieValue(array $values): string
    {
        return urlencode(json_encode($values));
    }

    private function makeTimestamp(string $modifier): int
    {
        return (new DateTimeImmutable($modifier, new DateTimeZone('UTC')))->getTimestamp();
    }
}

(new NetteCookieClosedEntriesStoreTest())->run();
