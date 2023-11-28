# Integration without a framework

* [Client initialization](#client-initialization)
  * [Cache](#cache)
  * [Custom Guzzle options](#custom-guzzle-options)
* [Fetching banners](#fetching-banners)
* [Rendering banners](#rendering-banners)
* [Latte templating system integration](#latte-templating-system-integration)

## Client initialization

The client is simply instanced as follows:

```php
use SixtyEightPublishers\AmpClient\AmpClient;
use SixtyEightPublishers\AmpClient\ClientConfig;

$config = ClientConfig::create('<amp-url>', '<amp-channel>');
$client = AmpClient::create($config);
```

The only mandatory values in the configuration are the AMP application URL and the channel (project) name.
Other optional options are as follows:

```php
use SixtyEightPublishers\AmpClient\AmpClient;
use SixtyEightPublishers\AmpClient\ClientConfig;
use SixtyEightPublishers\AmpClient\Request\ValueObject\BannerResource;

$config = ClientConfig::create('<amp-url>', '<amp-channel>');

# Configure http method, allowed values are GET (default) and POST.
$config = $config->withMethod('POST');

# Configure locale for requests (null by default).
$config = $config->withLocale('en');

# Configure AMP API version.
$config = $config->withVersion(1);

# Configure default resources for all requests.
$config = $config->withDefaultResources([
    new BannerResource('category', ['1']),
]);

# Configure value for http header X-Amp-Origin.
$config = $config->withOrigin('https://www.example.com');

# Configure http cache. More about the cache in the documentation below.
$config = $config->withCacheExpiration('1 hour');
$config->withCacheControlHeaderOverride('max-age=60');

$client = AmpClient::create($config);
```

> :exclamation: Please note that `ClientConfig` is immutable, just like the other client classes.

### Cache

By default, the client uses [NoCacheStorage](../src/Http/Cache/NoCacheStorage.php), so requests are not cached.
This can be changed by setting the cache and its expiration:

```php
use SixtyEightPublishers\AmpClient\AmpClient;
use SixtyEightPublishers\AmpClient\ClientConfig;
use SixtyEightPublishers\AmpClient\Http\Cache\InMemoryCacheStorage;

$config = ClientConfig::create('<amp-url>', '<amp-channel>')
    ->withCacheExpiration('1 hour');

$client = AmpClient::create($config)
    ->withCacheStorage(new InMemoryCacheStorage());
```

The cache expiration can be set using the DateTime modifier (for example `2 hours`, `1 day` etc.) or an integer that specifies the number of seconds for which the cache should be stored.
Currently, the following storages are implemented:

- [InMemoryCacheStorage](../src/Http/Cache/InMemoryCacheStorage.php)
- [NetteCacheStorage](../src/Bridge/Nette/NetteCacheStorage.php)

By default, the cache is controlled by the `Cache-Control` and `ETag` headers that AMP sends in the response.
However, the `Cache-Control` header can be overridden in the configuration:

```php
$config = $config->withCacheControlHeaderOverride('no-cache');
```

This setting will cache the responses, but a response is revalidated before each use.
The directives that are processed are `no-store`, `no-cache`, `max-age` and `s-maxage`. More information about the `Cache-Control` header [here](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Cache-Control).

### Custom Guzzle options

The client sends requests using Guzzle. If you would like Guzzle to give the client default options, you must instantiate the AMP client with the HTTP client factory.

```php
use SixtyEightPublishers\AmpClient\AmpClient;
use SixtyEightPublishers\AmpClient\ClientConfig;
use SixtyEightPublishers\AmpClient\Http\HttpClientFactory;
use SixtyEightPublishers\AmpClient\Response\Hydrator\ResponseHydrator;
use SixtyEightPublishers\AmpClient\Response\Hydrator\BannersResponseHydratorHandler;

$guzzleConfig = [
    # ... guzzle options ...
];

$config = ClientConfig::create('<amp-url>', '<amp-channel>');
$client = AmpClient::create(
    config: $config,
    httpClientFactory: new HttpClientFactory(
        responseHydrator: new ResponseHydrator([
            new BannersResponseHydratorHandler(),
        ]),
        guzzleClientConfig: $guzzleConfig,
    ),
);
```

## Fetching banners

```php
use SixtyEightPublishers\AmpClient\AmpClientInterface;
use SixtyEightPublishers\AmpClient\Request\BannersRequest;
use SixtyEightPublishers\AmpClient\Request\ValueObject\Position;
use SixtyEightPublishers\AmpClient\Request\ValueObject\BannerResource;

/** @var AmpClientInterface $client */

$request = new BannersRequest([
    new Position('homepage.top'),
    new Position('homepage.promo', [
        new BannerResource('role', 'guest'),
    ]),
]);

$response = $client->fetchBanners($request); # SixtyEightPublishers\AmpClient\Response\BannersResponse

$homepageTop = $response->getPosition('homepage.top');
$homepagePromo = $response->getPosition('homepage.promo');
```

## Rendering banners

Banners can be rendered simply by using the `Renderer` class:

```php
use SixtyEightPublishers\AmpClient\AmpClientInterface;
use SixtyEightPublishers\AmpClient\Renderer\Renderer;
use SixtyEightPublishers\AmpClient\Response\BannersResponse;

/** @var BannersResponse $response */

$renderer = Renderer::create();

echo $renderer->render($response->getPosition('homepage.top'));
```

The default templates are written as `.phtml` templates and can be found [here](../src/Renderer/Phtml/Templates). Templates can be also overwritten:

```php
use SixtyEightPublishers\AmpClient\Renderer\Renderer;
use SixtyEightPublishers\AmpClient\Renderer\Phtml\PhtmlRendererBridge;
use SixtyEightPublishers\AmpClient\Renderer\Templates;

$bridge = new PhtmlRendererBridge();
$bridge = $bridge->overrideTemplates(new Templates([
    Templates::TemplateSingle => '/my_custom_template_for_single_position.phtml',
]));

$renderer = Renderer::create($bridge);
```

The following template types can be overwritten:

```php
use SixtyEightPublishers\AmpClient\Renderer\Templates;

new Templates([
    Templates::TemplateSingle => '/single.phtml', # for positions with the display type "single"
    Templates::TemplateMultiple => '/multiple.phtml', # for positions with the display type "multiple"
    Templates::TemplateRandom => '/random.phtml', # for positions with the display type "random"
    Templates::TemplateNotFound => '/notFound.phtml',  # for positions that were not found
])
```

### Rendering banners using Latte

Banners can also be rendered using the [Latte](https://github.com/nette/latte) templating system.
Versions `^2.11` and `^3.0` are supported.

```php
use SixtyEightPublishers\AmpClient\AmpClientInterface;
use SixtyEightPublishers\AmpClient\Renderer\Renderer;
use SixtyEightPublishers\AmpClient\Renderer\Latte\LatteRendererBridge;
use SixtyEightPublishers\AmpClient\Response\BannersResponse;
use SixtyEightPublishers\AmpClient\Renderer\Latte\ClosureLatteFactory;
use Latte\Engine;

/** @var BannersResponse $response */

$renderer = Renderer::create(
    LatteRendererBridge::fromEngine(new Engine()),
);

# or lazily via

$renderer = Renderer::create(
    new LatteRendererBridge(
        new ClosureLatteFactory(function (): Engine {
            return new Engine();
        }),
    ),
);

echo $renderer->render($response->getPosition('homepage.top'));
```

The default `.latte` templates are located [here](../src/Renderer/Latte/Templates) and can be overridden in the same way as the default `.phtml` templates.

## Latte templating system integration

In addition to being able to render banners manually using Latte templates, the client offers the ability to render them directly using a custom Latte macro.
The macro is registered as follows:

```php
use SixtyEightPublishers\AmpClient\AmpClientInterface;
use SixtyEightPublishers\AmpClient\Renderer\RendererInterface;
use SixtyEightPublishers\AmpClient\Bridge\Latte\AmpClientLatteExtension;
use SixtyEightPublishers\AmpClient\Bridge\Latte\RendererProvider;
use Latte\Engine;

/** @var AmpClientInterface $client */
/** @var RendererInterface $renderer */

$engine = new Engine();
$provider = (new RendererProvider($client,$renderer))
    ->setDebugMode(true); # exceptions from Client and Renderer are suppressed in non-debug mode

AmpClientLatteExtension::register($engine, $provider);

$engine->render(__DIR__ . '/template.latte');
```

```latte
{* ./template.latte *}

{banner homepage.top}
{banner homepage.promo, ['role' => 'guest']}
```

Banners are now requested via API and rendered to the template automatically.

Each `{banner}` macro makes a separate request to the AMP API, so in our example above, two requests are sent.
This can be solved, however you need to render the Latte to a text string, not a buffer.

```php
use SixtyEightPublishers\AmpClient\AmpClientInterface;
use SixtyEightPublishers\AmpClient\Renderer\RendererInterface;
use SixtyEightPublishers\AmpClient\Bridge\Latte\AmpClientLatteExtension;
use SixtyEightPublishers\AmpClient\Bridge\Latte\RendererProvider;
use SixtyEightPublishers\AmpClient\Bridge\Latte\RenderingMode\QueuedRenderingMode;
use Latte\Engine;

/** @var AmpClientInterface $client */
/** @var RendererInterface $renderer */

$engine = new Engine();
$provider = (new RendererProvider($client,$renderer))
    ->setDebugMode(true) # exceptions from Client and Renderer are suppressed in non-debug mode
    ->setRenderingMode(new QueuedRenderingMode());

AmpClientLatteExtension::register($engine, $provider);

$output = $engine->renderToString(__DIR__ . '/template.latte');

echo $provider->renderQueuedPositions($output);
```

Now the client requests both banners in the template with one request.
