<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Tests;

use InvalidArgumentException;
use SixtyEightPublishers\AmpClient\ClientConfig;
use SixtyEightPublishers\AmpClient\Request\ValueObject\BannerResource;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/bootstrap.php';

final class ClientConfigTest extends TestCase
{
    public function testDefaultOptions(): void
    {
        $config = ClientConfig::create('https://www.example.com', 'test');

        Assert::same('https://www.example.com', $config->getUrl());
        Assert::same('test', $config->getChannel());
        Assert::same('GET', $config->getMethod());
        Assert::same(1, $config->getVersion());
        Assert::null($config->getLocale());
        Assert::same([], $config->getDefaultResources());
        Assert::null($config->getOrigin());
        Assert::same(0, $config->getCacheExpiration());
        Assert::null($config->getCacheControlHeaderOverride());
    }

    public function testUrlShouldBeChanged(): void
    {
        $config = ClientConfig::create('https://www.example.com', 'test');
        $modified = $config->withUrl('https://www.example.io');

        Assert::notSame($config, $modified);
        Assert::same('https://www.example.com', $config->getUrl());
        Assert::same('https://www.example.io', $modified->getUrl());
    }

    public function testChannelShouldBeChanged(): void
    {
        $config = ClientConfig::create('https://www.example.com', 'test');
        $modified = $config->withChannel('demo');

        Assert::notSame($config, $modified);
        Assert::same('test', $config->getChannel());
        Assert::same('demo', $modified->getChannel());
    }

    public function testMethodShouldBeChanged(): void
    {
        $config = ClientConfig::create('https://www.example.com', 'test');
        $modified = $config->withMethod('POST');

        Assert::notSame($config, $modified);
        Assert::same('GET', $config->getMethod());
        Assert::same('POST', $modified->getMethod());
    }

    public function testExceptionShouldBeThrownWhenInvalidMethodIsPassed(): void
    {
        $config = ClientConfig::create('https://www.example.com', 'test');

        Assert::exception(
            static fn () => $config->withMethod('PUT'),
            InvalidArgumentException::class,
            'Invalid method "PUT" passed.',
        );
    }

    public function testExceptionShouldBeThrownWhenInvalidVersionIsPassed(): void
    {
        $config = ClientConfig::create('https://www.example.com', 'test');

        Assert::exception(
            static fn () => $config->withVersion(1000),
            InvalidArgumentException::class,
            'Invalid version 1000 passed.',
        );
    }

    public function testLocaleShouldBeChanged(): void
    {
        $config = ClientConfig::create('https://www.example.com', 'test');
        $modified = $config->withLocale('cs');

        Assert::notSame($config, $modified);
        Assert::null($config->getLocale());
        Assert::same('cs', $modified->getLocale());
    }

    public function testDefaultResourceShouldBeChanged(): void
    {
        $config = ClientConfig::create('https://www.example.com', 'test');
        $resources = [
            new BannerResource('test', ['dummy']),
        ];
        $modified = $config->withDefaultResources($resources);

        Assert::notSame($config, $modified);
        Assert::same([], $config->getDefaultResources());
        Assert::same($resources, $modified->getDefaultResources());
    }

    public function testOriginShouldBeChanged(): void
    {
        $config = ClientConfig::create('https://www.example.com', 'test');
        $modified = $config->withOrigin('https://www.example.io');

        Assert::notSame($config, $modified);
        Assert::null($config->getOrigin());
        Assert::same('https://www.example.io', $modified->getOrigin());
    }

    public function testCacheExpirationShouldBeChanged(): void
    {
        $config = ClientConfig::create('https://www.example.com', 'test');
        $modified = $config->withCacheExpiration(3600);
        $modified2 = $config->withCacheExpiration('+1 hour');

        Assert::notSame($config, $modified);
        Assert::notSame($modified2, $modified);
        Assert::same(0, $config->getCacheExpiration());
        Assert::same(3600, $modified->getCacheExpiration());
        Assert::same('+1 hour', $modified2->getCacheExpiration());
    }

    public function testCacheControlHeaderOverrideShouldBeChanged(): void
    {
        $config = ClientConfig::create('https://www.example.com', 'test');
        $modified = $config->withCacheControlHeaderOverride('no-store');

        Assert::notSame($config, $modified);
        Assert::null($config->getCacheControlHeaderOverride());
        Assert::same('no-store', $modified->getCacheControlHeaderOverride());
    }
}

(new ClientConfigTest())->run();
