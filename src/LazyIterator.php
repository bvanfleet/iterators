<?php

namespace Iterators;

use Iterators\Consumers\SumConsumer;
use Iterators\Operations\FilterOperation;
use Iterators\Operations\KeysOperation;
use Iterators\Operations\MapOperation;
use Iterators\Operations\ReverseOperation;
use Iterators\Operations\SliceOperation;
use Iterators\Operations\UniqueOperation;
use Iterators\Operations\ValuesOperation;

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
        'sum' => SumConsumer::class,
    ];

    public static function from(iterable $source): self
    {
        return new self($source);
    }

    public function getIterator(): \Traversable
    {
        yield from $this->source;
    }

    public function __call(string $name, array $arguments): mixed
    {
        $class = self::$dispatch[$name] ?? null;

        if ($class === null) {
            throw new \BadMethodCallException(sprintf('Method "%s" is not supported.', $name));
        }

        $handler = new $class();

        if ($handler instanceof IteratorConsumer) {
            return $handler($this->source, ...$arguments);
        }

        if ($handler instanceof IteratorOperation) {
            return new self($handler($this->source, ...$arguments));
        }

        throw new \LogicException(sprintf('Invalid iterator handler configured for "%s".', $name));
    }
}
