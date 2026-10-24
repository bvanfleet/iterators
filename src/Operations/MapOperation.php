<?php

namespace Iterators\Operations;

use Iterators\IteratorOperation;

final class MapOperation implements IteratorOperation
{
    public function __invoke(iterable $items, mixed ...$args): iterable
    {
        $callback = $args[0] ?? null;

        if (! is_callable($callback)) {
            throw new \InvalidArgumentException('A valid callback function must be provided.');
        }

        foreach ($items as $key => $value) {
            yield $key => $callback($value, $key);
        }
    }
}
