<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Tests\Response\Hydrator;

use SixtyEightPublishers\AmpClient\Exception\ResponseHydrationException;
use SixtyEightPublishers\AmpClient\Response\Hydrator\ResponseHydrator;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../bootstrap.php';

final class ResponseHydratorTest extends TestCase
{
    public function testExceptionShouldBeThrownWhenNoHandlersDefined(): void
    {
        $hydrator = new ResponseHydrator([]);

        Assert::exception(
            static fn () => $hydrator->hydrate('stdClass', []),
            ResponseHydrationException::class,
            'Unable to handle response of type stdClass.',
        );
    }

    public function testExceptionShouldBeThrownWhenNoHandlerCanHydrateClassname(): void
    {
        $hydrator = new ResponseHydrator([
            new ResponseHydratorHandlerFixture('ArrayObject'),
        ]);

        Assert::exception(
            static fn () => $hydrator->hydrate('stdClass', []),
            ResponseHydrationException::class,
            'Unable to handle response of type stdClass.',
        );
    }

    public function testResponseShouldBeHydrated(): void
    {
        $result = (object) [
            'a' => 13,
        ];

        $hydrator = new ResponseHydrator([
            new ResponseHydratorHandlerFixture('stdClass', $result),
        ]);

        Assert::same($result, $hydrator->hydrate('stdClass', $result));
    }
}

(new ResponseHydratorTest())->run();
