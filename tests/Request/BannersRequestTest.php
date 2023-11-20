<?php

declare(strict_types=1);

namespace SixtyEightPublishers\AmpClient\Tests\Request;

use InvalidArgumentException;
use SixtyEightPublishers\AmpClient\Request\BannersRequest;
use SixtyEightPublishers\AmpClient\Request\ValueObject\Position;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../bootstrap.php';

final class BannersRequestTest extends TestCase
{
    public function testEmptyRequest(): void
    {
        $request = new BannersRequest();

        Assert::same([], $request->getPositions());
        Assert::null($request->getLocale());
    }

    public function testRequestImmutability(): void
    {
        $positionA = new Position('a');
        $positionB = new Position('b');
        $positionC = new Position('c');
        $positionD = new Position('d');

        $request = new BannersRequest([$positionA, $positionB]);

        $request2 = $request->withPosition($positionC);
        $request3 = $request2->withLocale('cs');
        $request4 = $request3->withPosition($positionD);

        Assert::same(['a' => $positionA, 'b' => $positionB], $request->getPositions());
        Assert::same(['a' => $positionA, 'b' => $positionB, 'c' => $positionC], $request2->getPositions());
        Assert::same(['a' => $positionA, 'b' => $positionB, 'c' => $positionC], $request3->getPositions());
        Assert::same(['a' => $positionA, 'b' => $positionB, 'c' => $positionC, 'd' => $positionD], $request4->getPositions());

        Assert::null($request->getLocale());
        Assert::null($request2->getLocale());
        Assert::same('cs', $request3->getLocale());
        Assert::same('cs', $request4->getLocale());
    }

    public function testExceptionShouldBeThrownWhenRequestWithDuplicatePositionsIsCreated(): void
    {
        Assert::exception(
            static fn () => new BannersRequest([
                new Position('a'),
                new Position('b'),
                new Position('a'),
            ]),
            InvalidArgumentException::class,
            'Position "a" has been already defined.',
        );
    }

    public function testExceptionShouldBeThrownWhenDuplicatedPositionIsAdded(): void
    {
        $request = new BannersRequest([
            new Position('a'),
            new Position('b'),
        ]);

        Assert::exception(
            static fn () => $request->withPosition(new Position('a')),
            InvalidArgumentException::class,
            'Position "a" has been already defined.',
        );
    }
}

(new BannersRequestTest())->run();
