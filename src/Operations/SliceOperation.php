<?php

namespace Iterators\Operations;

use Iterators\IteratorOperation;

final class SliceOperation implements IteratorOperation
{
    public function __invoke(iterable $items, mixed ...$args): iterable
    {
        $offset = $args[0] ?? 0;
        $length = $args[1] ?? null;
        $preserveKeys = $args[2] ?? false;

        if ($length !== null && $length < 0) {
            throw new \ValueError('Length must be greater than or equal to zero.');
        }

        $position = 0;
        $yielded = 0;

        foreach ($items as $key => $value) {
            if ($position++ < $offset) {
                continue;
            }

            if ($length !== null && $yielded >= $length) {
                break;
            }

            yield ($preserveKeys ? $key : $yielded) => $value;
            $yielded++;
        }
    }
}
