<?php

namespace Iterators\Tests\Unit;

use Iterators\Consumers\SumConsumer;
use Iterators\IteratorConsumer;
use Iterators\IteratorOperation;
use Iterators\LazyIterator;
use Iterators\Operations\FilterOperation;
use Iterators\Operations\KeysOperation;
use Iterators\Operations\MapOperation;
use Iterators\Operations\ReverseOperation;
use Iterators\Operations\SliceOperation;
use Iterators\Operations\UniqueOperation;
use Iterators\Operations\ValuesOperation;
use PHPUnit\Framework\TestCase;

final class LazyIteratorTest extends TestCase
{
    public function testMapOperationIsInvokableAndLazy(): void
    {
        $calls = 0;
        $operation = new MapOperation();
        $result = $operation([10, 20], function (int $value) use (&$calls): int {
            $calls++;
            return $value / 10;
        });

        self::assertSame(0, $calls);
        self::assertSame([0 => 1, 1 => 2], iterator_to_array($result));
        self::assertSame(2, $calls);
        self::assertInstanceOf(IteratorOperation::class, $operation);
    }

    public function testFilterSupportsBothValuesAndKeys(): void
    {
        $operation = new FilterOperation();

        self::assertSame(
            ['b' => 2],
            iterator_to_array($operation(['a' => 1, 'b' => 2], fn (int $value): bool => $value === 2)),
        );
        self::assertSame(
            ['b' => 2],
            iterator_to_array($operation(['a' => 1, 'b' => 2], fn (string $key): bool => $key === 'b', ARRAY_FILTER_USE_KEY)),
        );
        self::assertSame(
            ['b' => 2],
            iterator_to_array($operation(['a' => 1, 'b' => 2], fn (int $value, string $key): bool => $value === 2 && $key === 'b', ARRAY_FILTER_USE_BOTH)),
        );
    }

    public function testKeysValuesAndSliceOperations(): void
    {
        $items = ['first' => 10, 'second' => 20, 'third' => 30];

        self::assertSame(['second'], iterator_to_array((new KeysOperation())($items, 20, true)));
        self::assertSame([10, 20, 30], iterator_to_array((new ValuesOperation())($items)));
        self::assertSame([0 => 20, 1 => 30], iterator_to_array((new SliceOperation())($items, 1, null)));
        self::assertSame(['second' => 20], iterator_to_array((new SliceOperation())($items, 1, 1, true)));
    }

    public function testReverseAndUniqueOperations(): void
    {
        self::assertSame(
            [2 => 'third', 1 => 'second', 0 => 'first'],
            iterator_to_array((new ReverseOperation())(['first', 'second', 'third'])),
        );
        self::assertSame(
            ['a' => ['id' => 1], 'b' => ['id' => 2]],
            iterator_to_array((new UniqueOperation())([
                'a' => ['id' => 1],
                'b' => ['id' => 2],
                'c' => ['id' => 1],
            ], fn (array $item): int => $item['id'])),
        );
    }

    public function testLazyIteratorChainsOperations(): void
    {
        $result = iterator_to_array(
            LazyIterator::from(['a' => 1, 'b' => 2, 'c' => 3])
                ->filter(fn (int $value): bool => $value > 1)
                ->map(fn (int $value): int => $value * 10)
                ->values(),
        );

        self::assertSame([0 => 20, 1 => 30], $result);
    }

    public function testConsumerReturnsTerminalResult(): void
    {
        $consumer = new SumConsumer();

        self::assertInstanceOf(IteratorConsumer::class, $consumer);
        self::assertSame(10, $consumer([1, 2, 3, 4]));
        self::assertSame(10, LazyIterator::from([1, 2, 3, 4])->sum());
    }

    public function testUnsupportedMethodThrows(): void
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('Method "missing" is not supported.');

        LazyIterator::from([])->missing();
    }
}
