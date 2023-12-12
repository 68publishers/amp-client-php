<div align="center" style="text-align: center; margin-bottom: 50px">
<img src="images/logo.png" alt="AMP Client PHP Logo" align="center" width="100">
<h1>AMP Client PHP</h1>
<h2 align="center">Integration with Nette framework</h2>
</div>

For more information on how the client works, we also recommend reading the [Integration without a framework](./integration-without-framework.md) section.

* [Client integration](#client-integration)
* [Latte macros integration](#latte-macros-integration)
  * [Using multiple rendering modes](#using-multiple-rendering-modes)
  * [Configuring client before the first fetch](#configuring-client-before-the-first-fetch)
  * [Renaming the macro](#renaming-the-macro)

## Client integration

The minimum configuration is as follows:

```neon
extensions:
    amp_client: SixtyEightPublishers\AmpClient\Bridge\Nette\DI\AmpClientExtension

amp_client:
    url: <amp-url>
    channel: <amp-channel>
```

The only mandatory values in the configuration are the AMP application URL and the channel (project) name.
Here are all the configuration options:

```neon
extensions:
    amp_client: SixtyEightPublishers\AmpClient\Bridge\Nette\DI\AmpClientExtension

amp_client:
    url: <amp-url>
    channel: <amp-channel>
    # Http method, allowed values are GET (default) and POST
    method: GET 
    # Locale for requests (null by default):
    locale: en
    # AMP API version:
    version: 1
    # Default resources for all requests:
    default_resources:
        category:
            - 1
    # Value for http header X-Amp-Origin.
    origin: https://www.example.com

    cache:
        # Cache store, by default null (cache is disabled):
        storage: @Nette\Caching\Storage
        # Expiration must be set for caching:
        expiration: '1 hour'
        # Overrides Cache-Control header in responses from the AMP:
        cache_control_header_override: 'max-age=60'

    http:
        # Custom Guzzle options:
        guzzle_config: []

    renderer:
        # "phtml" or "latte". The bridge is automatically resolved. If you are working on standard Nette application the bridge will be always "latte"
        bridge: latte
        # Here can be overriden the default templates for each position type:
        templates:
            single: %appDir%/templates/amp/single.latte
            random: %appDir%/templates/amp/random.latte
            multiple: %appDir%/templates/amp/multiple.latte
            not_found: %appDir%/templates/amp/not_found.latte
            client_side: %appDir%/templates/amp/client_side.latte
```

Two important services are now available in the DI Container - `AmpClientInterface` and `RendererInterface`.
You can autowire them into, for example, Presenter or any service:

```php
use Nette\Application\UI\Presenter;
use SixtyEightPublishers\AmpClient\AmpClientInterface;
use SixtyEightPublishers\AmpClient\Renderer\RendererInterface;

final class MyPresenter extends Presenter {
    public function __construct(
        private readonly AmpClientInterface $client,
        private readonly RendererInterface $renderer,
    ) {
        parent::__construct();
    }

    public function actionDefault(): void {
        $request = new BannersRequest([
            new Position('homepage.top'),
            new Position('homepage.promo', [
                new BannerResource('role', 'guest'),
            ]),
        ]);

        $response = $this->client->fetchBanners($request);

        bdump($this->renderer->render($response->getPosition('homepage.top')));
        bdump($this->renderer->render($response->getPosition('homepage.promo')));
    }
}
```

## Latte macros integration

Banners can be rendered directly from the Latte template without having to manually call the client. Another extension must be registered for this:

```neon
extensions:
    amp_client.latte: SixtyEightPublishers\AmpClient\Bridge\Nette\DI\AmpClientLatteExtension(%debugMode%)
```

Now the macro `{banner}` is available in the application and can be used in templates:

```latte
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

Each `{banner}` macro makes a separate request to the AMP API, so in our example above, three requests are sent.
This can be solved by the following configuration:

```neon
amp_client.latte:
    rendering_mode: queued_in_presenter_context # the default value is "direct"
```

Now when rendering a page via `nette/application`, information about all banners to be rendered is collected and a request to the AMP API is sent only once the whole template is rendered.
The banners are then inserted back into the rendered page. This behavior also works automatically with AJAX snippets.

The following rendering modes are available:

- **direct** ([DirectRenderingMode](../src/Bridge/Latte/RenderingMode/DirectRenderingMode.php)) - The default mode, API is requested separately for each banner.
- **client_side** ([ClientSideRenderingMode](../src/Bridge/Latte/RenderingMode/ClientSideRenderingMode.php)) - Renders only a wrapper element and leaves loading banners on the JavaScript client. Banners are loaded by calling the `attachBanners()` function.
- **embed** ([EmbedRenderingMode](../src/Bridge/Latte/RenderingMode/EmbedRenderingMode.php)) - Same behavior as `client_side`, but passes the information to the JavaScript client that the banner should be rendered as `embed`.
- **queued_in_presenter_context** ([QueuedRenderingInPresenterContextMode](../src/Bridge/Latte/RenderingMode/QueuedRenderingInPresenterContextMode.php)) - Renders only HTML comments as placeholders and stores requested positions in a queue. It will request, render and place all banners to them positions at once before the presenter returns a response.
- **queued** ([QueuedRenderingMode](../src/Bridge/Latte/RenderingMode/QueuedRenderingMode.php)) - Same behavior as `queued_in_presenter_context`, but it doesn't take into account whether the website template is currently being rendered through the Nette application. It is more suited for an integration without a framework.

### Using multiple rendering modes

Besides the default rendering mode, which is set by the option `rendering_mode`, it is possible to configure alternative modes that can be used in templates.

```neon
amp_client.latte:
    rendering_mode: queued_in_presenter_context # the default value is "direct"
    alternative_rendering_modes:
        - client_side
```

```latte
{* The first banner will be rendered with the default mode *}
{banner homepage.top}

{* The second banner will be rendered client side *}
{banner homepage.promo, mode: 'client_side'}
```

### Configuring client before the first fetch

Occasionally, we may want to configure the client before making a request to the AMP API from the template.
For example, we left the `locale` blank in the main `neon` configuration and want to set it up at runtime.
To do this, we can use a custom service implementing the `ConfigureClientEventHandlerInterface` interface.

```php
use SixtyEightPublishers\AmpClient\Bridge\Latte\Event\ConfigureClientEvent;
use SixtyEightPublishers\AmpClient\Bridge\Latte\Event\ConfigureClientEventHandlerInterface;

final class SetupLocaleEventHandler implements ConfigureClientEventHandlerInterface
{
    public function __construct(
        private readonly MyLocalizationService $localizationService,
    ) {}

    public function __invoke(ConfigureClientEvent $event): ConfigureClientEvent
    {
        $client = $event->getClient();
        $config = $client->getConfig();
        
        return $event->withClient(
            $client->withConfig(
                $config->withLocale($this->localizationService->getCurrentLocale()),
            ),
        ); 
    }
}
```

And register it:

```neon
services:
    - SetupLocaleEventHandler
    # or
    -
        autowired: self
        type: SetupLocaleEventHandler
```

Our handler will be called before the first AMP API call from the Latte.

### Renaming the macro

Macro `{banner}` can be renamed. The following configuration will rename it to `{ampBanner}`.

```neon
amp_client.latte:
    banner_macro_name: ampBanner
```
