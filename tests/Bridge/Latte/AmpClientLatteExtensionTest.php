<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Tests\Bridge\Latte;

use Latte\Engine;
use Latte\Loaders\StringLoader;
use Mockery;
use SixtyEightPublishers\AmpClient\Bridge\Latte\AmpClientLatteExtension;
use SixtyEightPublishers\AmpClient\Bridge\Latte\RendererProvider;
use stdClass;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../bootstrap.php';

final class AmpClientLatteExtensionTest extends TestCase
{
    /**
     * @dataProvider latteTemplatesDataProvider
     */
    public function testMacro(
        string $latteCode,
        array $expectedProviderArguments,
        ?string $tagName
    ): void {
        $latte = new Engine();
        $provider = Mockery::mock(RendererProvider::class);

        if (null !== $tagName) {
            AmpClientLatteExtension::register($latte, $provider, $tagName);
        } else {
            AmpClientLatteExtension::register($latte, $provider);
        }

        $provider
            ->shouldReceive('__invoke')
            ->once()
            ->with(Mockery::type(stdClass::class), ...$expectedProviderArguments)
            ->andReturn('');

        $latte->setLoader(new StringLoader());

        Assert::noError(
            static fn () => $latte->renderToString($latteCode),
        );
    }

    public function latteTemplatesDataProvider(): array
    {
        # 0 => Latte code
        # 1 => expected arguments for RendererProvider::__invoke (except `globals`)
        # 2 => Custom macro name

        return [
            'Position as unquoted string' => [
                0 => <<<'LATTE'
                {banner homepage.top}
                LATTE,
                1 => [
                    'homepage.top',
                ],
                2 => null,
            ],
            'Position as unquoted string and custom macro name' => [
                0 => <<<'LATTE'
                {ampBanner homepage.top}
                LATTE,
                1 => [
                    'homepage.top',
                ],
                2 => 'ampBanner',
            ],
            'Position as quoted string' => [
                0 => <<<'LATTE'
                {banner 'homepage.top'}
                LATTE,
                1 => [
                    'homepage.top',
                ],
                2 => null,
            ],
            'Position as variable' => [
                0 => <<<'LATTE'
                {var $position = 'homepage.top'}
                {banner $position}
                LATTE,
                1 => [
                    'homepage.top',
                ],
                2 => null,
            ],
            'Resources as array' => [
                0 => <<<'LATTE'
                {banner homepage.top, [product => '123', category => ['123', '456']]}
                LATTE,
                1 => [
                    'homepage.top',
                    ['product' => '123', 'category' => ['123', '456']],
                ],
                2 => null,
            ],
            'Resources as variable' => [
                0 => <<<'LATTE'
                {var $resources = [product => '123', category => ['123', '456']]}
                {banner homepage.top, $resources}
                LATTE,
                1 => [
                    'homepage.top',
                    ['product' => '123', 'category' => ['123', '456']],
                ],
                2 => null,
            ],
        ];
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }
}

(new AmpClientLatteExtensionTest())->run();
