<?php

namespace Iterators\Operations;

use Iterators\IteratorOperation;

final class ReverseOperation implements IteratorOperation
{
    public function __invoke(iterable $items, mixed ...$args): iterable
    {
        $preserveKeys = $args[0] ?? true;
        $values = iterator_to_array($items, true);

        foreach (array_reverse($values, $preserveKeys) as $key => $value) {
            yield $key => $value;
        }
    }
}
