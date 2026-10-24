<?php

namespace Iterators\Operations;

use Iterators\IteratorOperation;

final class ValuesOperation implements IteratorOperation
{
    public function __invoke(iterable $items, mixed ...$args): iterable
    {
        foreach ($items as $value) {
            yield $value;
        }
    }
}
