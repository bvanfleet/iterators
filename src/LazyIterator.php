<?php

namespace Iterators;

use Iterators\Consumers\CountValuesConsumer;
use Iterators\Consumers\SumConsumer;
use Iterators\Operations\ChunkOperation;
use Iterators\Operations\ColumnOperation;
use Iterators\Operations\CombineOperation;
use Iterators\Operations\DiffAssocOperation;
use Iterators\Operations\DiffKeyOperation;
use Iterators\Operations\DiffOperation;
use Iterators\Operations\FilterOperation;
use Iterators\Operations\FlipOperation;
use Iterators\Operations\IntersectAssocOperation;
use Iterators\Operations\IntersectKeyOperation;
use Iterators\Operations\IntersectOperation;
use Iterators\Operations\KeysOperation;
use Iterators\Operations\MapOperation;
use Iterators\Operations\MergeOperation;
use Iterators\Operations\PadOperation;
use Iterators\Operations\ReplaceOperation;
use Iterators\Operations\ReplaceRecursiveOperation;
use Iterators\Operations\ReverseOperation;
use Iterators\Operations\SliceOperation;
use Iterators\Operations\UniqueOperation;
use Iterators\Operations\ValuesOperation;

/**
 * @method self chunk(int $length, bool $preserve_keys = false)
 * @method self column(int|string|null $column_key, int|string|null $index_key = null)
 * @method self flip()
 * @method self combine(array $values)
 * @method self diff(array ...$arrays)
 * @method self diff_assoc(array ...$arrays)
 * @method self diff_key(array ...$arrays)
 * @method self intersect(array ...$arrays)
 * @method self intersect_assoc(array ...$arrays)
 * @method self intersect_key(array ...$arrays)
 * @method self merge(array ...$arrays)
 * @method self replace(array ...$replacements)
 * @method self replace_recursive(array ...$replacements)
 * @method self pad(int $length, mixed $value)
 */
class LazyIterator implements \IteratorAggregate
{
    private function __construct(private readonly iterable $source)
    {
    }

    private static array $dispatch = [
        'map' => MapOperation::class,
        'filter' => FilterOperation::class,
        'keys' => KeysOperation::class,
        'values' => ValuesOperation::class,
        'slice' => SliceOperation::class,
        'reverse' => ReverseOperation::class,
        'unique' => UniqueOperation::class,
        'chunk' => ChunkOperation::class,
        'column' => ColumnOperation::class,
        'flip' => FlipOperation::class,
        'combine' => CombineOperation::class,
        'diff' => DiffOperation::class,
        'diff_assoc' => DiffAssocOperation::class,
        'diff_key' => DiffKeyOperation::class,
        'intersect' => IntersectOperation::class,
        'intersect_assoc' => IntersectAssocOperation::class,
        'intersect_key' => IntersectKeyOperation::class,
        'merge' => MergeOperation::class,
        'replace' => ReplaceOperation::class,
        'replace_recursive' => ReplaceRecursiveOperation::class,
        'pad' => PadOperation::class,
        'sum' => SumConsumer::class,
        'count_values' => CountValuesConsumer::class,
    ];

    public static function from(iterable $source): self
    {
        return new self($source);
    }

    public function getIterator(): \Traversable
    {
        yield from $this->source;
    }

    public function toArray(): array
    {
        return iterator_to_array($this->source);
    }

    public function __call(string $name, array $arguments): mixed
    {
        $class = self::$dispatch[$name] ?? null;

        if ($class === null) {
            throw new \BadMethodCallException(sprintf('Method "%s" is not supported.', $name));
        }

        $handler = new $class();
        return match(true) {
            $handler instanceof IteratorConsumer => $handler($this->source, ...$arguments),
            $handler instanceof IteratorOperation => new self($handler($this->source, ...$arguments)),
            default => throw new \LogicException(sprintf('Invalid iterator handler configured for "%s".', $name)),
        };
    }
}
