<?php

namespace Iterators\Operations;

use Iterators\IteratorOperation;

final class KeysOperation implements IteratorOperation
{
    public function __invoke(iterable $items, mixed ...$args): iterable
    {
        $hasSearchValue = count($args) > 0;
        $searchValue = $args[0] ?? null;
        $strict = $args[1] ?? false;

        foreach ($items as $key => $value) {
            if ($hasSearchValue && ! ($strict ? $value === $searchValue : $value == $searchValue)) {
                continue;
            }

            yield $key;
        }
    }
}
