<?php

namespace Iterators\Operations;

final class DiffOperation extends AbstractNativeArrayOperation
{
    protected function apply(array $items, mixed ...$args): array
    {
        return \array_diff($items, ...$args);
    }
}
