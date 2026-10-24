<?php

namespace Iterators\Tests\Unit;

use Iterators\Consumers\CountValuesConsumer;
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
use PHPUnit\Framework\Attributes\DataProvider;
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
        $result = LazyIterator::from(['a' => 1, 'b' => 2, 'c' => 3])
            ->filter(fn (int $value): bool => $value > 1)
            ->map(fn (int $value): int => $value * 10)
            ->values()
            ->toArray();

        self::assertSame([0 => 20, 1 => 30], $result);
    }

    #[DataProvider('arrayOperationCases')]
    public function testArrayOperationsMatchPhpBuiltIns(string $method, array $items, array $args): void
    {
        self::assertSame(
            call_user_func_array('array_' . $method, [$items, ...$args]),
            LazyIterator::from($items)->{$method}(...$args)->toArray(),
            sprintf('The %s operation should match its PHP built-in.', $method),
        );
    }

    public function testArrayOperationsSupportNativeNamedArguments(): void
    {
        self::assertSame(
            \array_chunk(['a' => 1, 'b' => 2, 'c' => 3], length: 2, preserve_keys: true),
            LazyIterator::from(['a' => 1, 'b' => 2, 'c' => 3])->chunk(length: 2, preserve_keys: true)->toArray(),
        );
        self::assertSame(
            \array_column([['id' => 10, 'name' => 'red']], column_key: 'name', index_key: 'id'),
            LazyIterator::from([['id' => 10, 'name' => 'red']])->column(column_key: 'name', index_key: 'id')->toArray(),
        );
    }

    public function testArrayOperationsAcceptGeneratorSources(): void
    {
        $source = static function (): \Generator {
            yield 3 => 'first';
            yield 7 => 'second';
            yield 9 => 'third';
        };

        self::assertSame(
            \array_chunk([3 => 'first', 7 => 'second', 9 => 'third'], 2, true),
            LazyIterator::from($source())->chunk(2, true)->toArray(),
        );
    }

    public function testConsumerReturnsTerminalResult(): void
    {
        $consumer = new SumConsumer();

        self::assertInstanceOf(IteratorConsumer::class, $consumer);
        self::assertSame(10, $consumer([1, 2, 3, 4]));
        self::assertSame(10, LazyIterator::from([1, 2, 3, 4])->sum());
    }

    public function testCountValuesConsumerCountsIntegerAndStringValues(): void
    {
        $consumer = new CountValuesConsumer();

        self::assertInstanceOf(IteratorConsumer::class, $consumer);
        self::assertSame(
            ['apple' => 2, 1 => 2, 'banana' => 1],
            $consumer(['apple', 1, 'banana', 'apple', '1']),
        );
        self::assertSame(
            ['apple' => 2, 1 => 2, 'banana' => 1],
            LazyIterator::from((static function (): \Generator {
                yield 'apple';
                yield 1;
                yield 'banana';
                yield 'apple';
                yield '1';
            })())->count_values(),
        );
    }

    public function testUnsupportedMethodThrows(): void
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('Method "missing" is not supported.');

        LazyIterator::from([])->missing();
    }

    public static function arrayOperationCases(): iterable
    {
        yield 'chunk' => ['chunk', ['a' => 1, 'b' => 2, 'c' => 3], [2, true]];
        yield 'column' => ['column', [['id' => 10, 'name' => 'red'], ['id' => 20, 'name' => 'blue']], ['name', 'id']];
        yield 'flip' => ['flip', ['red' => 'r', 'blue' => 'b'], []];
        yield 'combine' => ['combine', ['red', 'blue'], [['r', 'b']]];
        yield 'diff' => ['diff', ['a' => 1, 'b' => 2, 'c' => 3], [[2]]];
        yield 'diff_assoc' => ['diff_assoc', ['a' => 1, 'b' => 2], [['a' => 1]]];
        yield 'diff_key' => ['diff_key', ['a' => 1, 'b' => 2], [['a' => 3]]];
        yield 'intersect' => ['intersect', ['a' => 1, 'b' => 2, 'c' => 3], [[2, 3]]];
        yield 'intersect_assoc' => ['intersect_assoc', ['a' => 1, 'b' => 2], [['b' => 2]]];
        yield 'intersect_key' => ['intersect_key', ['a' => 1, 'b' => 2], [['b' => 3]]];
        yield 'merge' => ['merge', ['name' => 'red', 0 => 'first'], [['name' => 'blue', 1 => 'second']]];
        yield 'replace' => ['replace', ['a' => 1, 'b' => 2], [['b' => 3, 'c' => 4]]];
        yield 'replace_recursive' => ['replace_recursive', ['colors' => ['primary' => 'red', 'secondary' => 'blue']], [['colors' => ['primary' => 'green']]]];
        yield 'pad' => ['pad', [1, 2], [4, 0]];
    }
}
