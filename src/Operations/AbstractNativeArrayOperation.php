<?php

namespace Iterators\Operations;

use Iterators\IteratorOperation;

abstract class AbstractNativeArrayOperation implements IteratorOperation
{
    public function __invoke(iterable $items, mixed ...$args): iterable
    {
        $array = [];

        foreach ($items as $key => $value) {
            $array[$key] = $value;
        }

        yield from $this->apply($array, ...$args);
    }

    abstract protected function apply(array $items, mixed ...$args): array;
}
