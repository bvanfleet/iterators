<?php

namespace Iterators\Operations;

use Iterators\IteratorOperation;

final class FilterOperation implements IteratorOperation
{
    public function __invoke(iterable $items, mixed ...$args): iterable
    {
        $callback = $args[0] ?? null;
        $mode = $args[1] ?? 0;

        if (! is_null($callback) && ! is_callable($callback)) {
            throw new \InvalidArgumentException('A valid callback function must be provided.');
        }

        if (! in_array($mode, [0, ARRAY_FILTER_USE_KEY, ARRAY_FILTER_USE_BOTH], true)) {
            throw new \ValueError('Invalid array_filter mode.');
        }

        foreach ($items as $key => $value) {
            $keep = match ($mode) {
                ARRAY_FILTER_USE_KEY => $callback ? $callback($key) : $value,
                ARRAY_FILTER_USE_BOTH => $callback ? $callback($value, $key) : $value,
                default => $callback ? $callback($value) : $value,
            };

            if ($keep) {
                yield $key => $value;
            }
        }
    }
}
