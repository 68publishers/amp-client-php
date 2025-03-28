<div align="center" style="text-align: center; margin-bottom: 50px">
<img src="images/logo.png" alt="AMP Client PHP Logo" align="center" width="100">
<h1>AMP Client PHP</h1>
<h2 align="center">Integration without a framework</h2>
</div>

* [Client initialization](#client-initialization)
  * [Cache](#cache)
  * [Custom Guzzle options](#custom-guzzle-options)
* [Fetching banners](#fetching-banners)
* [Rendering banners](#rendering-banners)
  * [Rendering banners on the client side](#rendering-banners-on-the-client-side)
  * [Rendering embed (iframe) banners](#rendering-embed-iframe-banners)
  * [Lazy loading of image banners](#lazy-loading-of-image-banners)
  * [Templates overwriting](#templates-overwriting)
  * [Rendering banners using Latte](#rendering-banners-using-latte)
* [Latte templating system integration](#latte-templating-system-integration)
  * [Using multiple rendering modes](#using-multiple-rendering-modes)
  * [Renaming the macro](#renaming-the-macro)

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
use SixtyEightPublishers\AmpClient\Renderer\Renderer;
use SixtyEightPublishers\AmpClient\Response\BannersResponse;

/** @var BannersResponse $response */

$renderer = Renderer::create();

echo $renderer->render($response->getPosition('homepage.top'));
```

The second argument can be used to pass an array of attributes to be contained in the banner's HTML wrapper element.

```php
echo $renderer->render($response->getPosition('homepage.top'), ['class' => 'my-awesome-class']);
```

Attributes can also be rendered conditionally, the client currently supports the following conditions:

- `exists@<attribute>` - The attribute will only be created if a banner exists and has some content.
- `exists(<breakpoint>)@<attribute>` - The attribute will only be created if a banner contains content for the specified breakpoint.

```php
echo $renderer->render($response->getPosition('homepage.top'), [
    'class' => 'my-awesome-class'
    'exists@class' => 'banner-exists',
    'exists(default)@class' => 'banner-default',
    'exists(600)@class' => 'banner-600',
]);
```
The `class` attribute is the only one that will be merged in the result. The other attributes are not merged and are overwritten.

The third argument can be used to provide custom options.
These options are available in the banner templates and will also be available to the JavaScript client, so they can be accessed in event handlers.

```php
echo $renderer->render($response->getPosition('homepage.top'), [], ['customOption' => 'customValue']);
```

### Rendering banners on the client side

Banner rendering can be left to the [JavaScript client](https://github.com/68publishers/amp-client-js) using the `Renderer::renderClientSide()` method.

```php
use SixtyEightPublishers\AmpClient\Renderer\Renderer;
use SixtyEightPublishers\AmpClient\Request\ValueObject\Position;

$renderer = Renderer::create();

echo $renderer->renderClientSide(new Position('homepage.top'));
echo $renderer->renderClientSide(new Position('homepage.promo'), ['class' => 'my-awesome-class']);
```

Banners rendered in this way will be loaded by the JavaScript client when its `attachBanners()` function is called.

### Rendering embed (iframe) banners

Banners can be rendered in "embed" mode, which means they are inside the `<iframe>` tag.
This rendering mode is again controlled by the JavaScript client.

```php
use SixtyEightPublishers\AmpClient\Renderer\Renderer;
use SixtyEightPublishers\AmpClient\Request\ValueObject\Position;
use SixtyEightPublishers\AmpClient\Renderer\ClientSideMode;

$renderer = Renderer::create();

echo $renderer->renderClientSide(
    position: new Position('homepage.top'),
    mode: ClientSideMode::embed(),
);
```

The information that the banner should be rendered in the `<iframe>` tag can also be returned by AMP.
If we want to follow this behavior, we need to condition the rendering ourselves.

```php
use SixtyEightPublishers\AmpClient\AmpClientInterface;
use SixtyEightPublishers\AmpClient\Renderer\RendererInterface;
use SixtyEightPublishers\AmpClient\Request\BannersRequest;
use SixtyEightPublishers\AmpClient\Request\ValueObject\Position;
use SixtyEightPublishers\AmpClient\Renderer\ClientSideMode;

/** @var AmpClientInterface $client */
/** @var RendererInterface $renderer */

$request = new BannersRequest([
    new Position('homepage.top'),
]);

$response = $client->fetchBanners($request);
$position = $response->getPosition('homepage.top');

if ($position::ModeEmbed === $position->getMode()) {
    echo $renderer->renderClientSide(
        position: $request->getPosition('homepage.top'),
        mode: ClientSideMode::embed(),
    );
} else {
    echo $renderer->render($position);
}
```

⚠️ Only image banners on `single` and `random` positions are now fully compatible with `embed` mode. Rendering other types via `embed` mode is not recommended.

### Lazy loading of image banners

The default client templates support [native lazy loading](https://developer.mozilla.org/en-US/docs/Web/Performance/Lazy_loading#images_and_iframes) of images.
To activate lazy loading the option `'loading' => 'lazy'` must be passed to the renderer.

```php
# server-side rendering:
echo $renderer->render($response->getPosition('homepage.top'), [], [
    'loading' => 'lazy',
]);

# client-side rendering:
echo $renderer->renderClientSide(new Position('homepage.top'), [], [
    'loading' => 'lazy',
]);
```

A special case is a position of type `multiple`, where it may be desirable to lazily load all banners except the first.
his can be achieved with the following expression:

```php
# server-side rendering:
echo $renderer->render($response->getPosition('homepage.top'), [], [
    'loading' => '>=1:lazy',
]);

# client-side rendering:
echo $renderer->renderClientSide(new Position('homepage.top'), [], [
    'loading' => '>=1:lazy',
]);
```

If you prefer a different implementation of lazy loading, it is possible to use own templates instead of the default ones and integrate a different solution in these templates.

#### Fetch priority of image banners

The [fetchpriority](https://developer.mozilla.org/en-US/docs/Web/API/HTMLImageElement/fetchPriority) attribute can be set for image and embed banners using the `fetchpriority` option.

```php
# server-side rendering:
echo $renderer->render($response->getPosition('homepage.top'), [], [
    'fetchpriority' => 'high',
]);

# client-side rendering:
echo $renderer->renderClientSide(new Position('homepage.top'), [], [
    'fetchpriority' => 'high',
]);
```

In the case of a `multiple` position, it may be required that the first banner have a fetch priority of `high` and the others `low`.
This can be achieved with the following expression:

```php
# server-side rendering:
echo $renderer->render($response->getPosition('homepage.top'), [], [
    'fetchpriority' => '0:high,low',
]);

# client-side rendering:
echo $renderer->renderClientSide(new Position('homepage.top'), [], [
    'fetchpriority' => '0:high,low',
]);
```

### Templates overwriting

The default templates are written as `.phtml` templates and can be found [here](../src/Renderer/Phtml/Templates). Templates can be also overwritten:

```php
use SixtyEightPublishers\AmpClient\Renderer\Renderer;
use SixtyEightPublishers\AmpClient\Renderer\Phtml\PhtmlRendererBridge;
use SixtyEightPublishers\AmpClient\Renderer\Templates;

$bridge = new PhtmlRendererBridge();
$bridge = $bridge->overrideTemplates(new Templates([
    Templates::Single => '/my_custom_template_for_single_position.phtml',
]));

$renderer = Renderer::create($bridge);
```

The following template types can be overwritten:

```php
use SixtyEightPublishers\AmpClient\Renderer\Templates;

new Templates([
    Templates::Single => '/single.phtml', # for positions with the display type "single"
    Templates::Multiple => '/multiple.phtml', # for positions with the display type "multiple"
    Templates::Random => '/random.phtml', # for positions with the display type "random"
    Templates::NotFound => '/notFound.phtml',  # for positions that were not found
    Templates::ClientSide => '/clientSide.phtml', # for positions that should be rendered by JS client
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

{*
    Available arguments are:
    * `resources` - An array of banner resources,
    * `options` - An array of custom options. Can be also used for enabling native lazy loading.
    * `attributes` - An array of HTML attributes
    * `mode` - Allows to switch a rendering mode. See the "Using multiple rendering modes" section below
*}

{banner homepage.top}
{banner homepage.promo, resources: ['role' => 'guest'], options: ['loading' => 'lazy']}
{banner homepage.bottom, attributes: ['class' => 'my-awesome-class']}
```

Banners are now requested via API and rendered to the template automatically.

By default, each `{banner}` macro makes a separate request to the AMP API, so in our example above, three requests are sent.
This can be solved, however you need to render the Latte to a string, not a buffer.

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
    ->setDebugMode(true)
    ->setRenderingMode(new QueuedRenderingMode());

AmpClientLatteExtension::register($engine, $provider);

$output = $engine->renderToString(__DIR__ . '/template.latte');

echo $provider->renderQueuedPositions($output);
```

Now the client requests both banners in the template with one request.

The following rendering modes are available:

- **direct** ([DirectRenderingMode](../src/Bridge/Latte/RenderingMode/DirectRenderingMode.php)) - The default mode, API is requested separately for each banner.
- **client_side** ([ClientSideRenderingMode](../src/Bridge/Latte/RenderingMode/ClientSideRenderingMode.php)) - Renders only a wrapper element and leaves loading banners on the JavaScript client. Banners are loaded by calling the `attachBanners()` function.
- **embed** ([EmbedRenderingMode](../src/Bridge/Latte/RenderingMode/EmbedRenderingMode.php)) - Same behavior as `client_side`, but passes the information to the JavaScript client that the banner should be rendered as `embed`.
- **queued** ([QueuedRenderingMode](../src/Bridge/Latte/RenderingMode/QueuedRenderingMode.php)) - Renders only HTML comments as placeholders and stores requested positions in a queue. It will request and render all banners at once when the `RendererProvider::renderQueuedPositions()` method is called.
- **queued_in_presenter_context** ([QueuedRenderingInPresenterContextMode](../src/Bridge/Latte/RenderingMode/QueuedRenderingInPresenterContextMode.php)) - Same behavior as `queued` but in the context of a Presenter only. Usable with Nette applications only.

### Using multiple rendering modes

Besides the default rendering mode, which is set by the method `RendererProvider::setRenderingMode()`, it is possible to configure alternative modes that can be used in templates.

```php
use SixtyEightPublishers\AmpClient\Bridge\Latte\RendererProvider;
use SixtyEightPublishers\AmpClient\Bridge\Latte\RenderingMode\DirectRenderingMode;
use SixtyEightPublishers\AmpClient\Bridge\Latte\RenderingMode\ClientSideRenderingMode;

/** @var AmpClientInterface $client */
/** @var RendererInterface $renderer */

$provider = new RendererProvider($client, $renderer);

$provider->setRenderingMode(new DirectRenderingMode()); # No need to actually set it up, this mode is the default.
$provider->setAlternativeRenderingModes([
    new ClientSideRenderingMode(),
]);
```

```latte
{* The first banner will be rendered with the default mode (directly) *}
{banner homepage.top}

{* The second banner will be rendered client side *}
{banner homepage.promo, mode: 'client_side'}
```

### Renaming the macro

Macro `{banner}` can be renamed. This can be done by specifying the third argument of the method `AmpClientLatteExtension::register()`.

```php
use SixtyEightPublishers\AmpClient\Bridge\Latte\AmpClientLatteExtension;
use SixtyEightPublishers\AmpClient\Bridge\Latte\RendererProvider;
use Latte\Engine;

/** @var RendererProvider $provider */
/** @var Engine $engine */

AmpClientLatteExtension::register($engine, $provider, 'ampBanner');
```

The macro will now be named `{ampBanner}`.
