<?php

namespace Iterators\Operations;

use Iterators\IteratorOperation;

final class UniqueOperation implements IteratorOperation
{
    public function __invoke(iterable $items, mixed ...$args): iterable
    {
        $keySelector = $args[0] ?? null;

        if ($keySelector !== null && ! is_callable($keySelector)) {
            throw new \InvalidArgumentException('A valid key selector must be provided.');
        }

        $seen = [];

        foreach ($items as $key => $value) {
            $candidate = $keySelector
                ? $keySelector($value, $key)
                : $value;

            foreach ($seen as $previous) {
                if ($previous === $candidate) {
                    continue 2;
                }
            }

            $seen[] = $candidate;
            yield $key => $value;
        }
    }
}
