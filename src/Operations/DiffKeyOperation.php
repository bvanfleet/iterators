<?php

namespace Iterators\Operations;

final class DiffKeyOperation extends AbstractNativeArrayOperation
{
    protected function apply(array $items, mixed ...$args): array
    {
        return \array_diff_key($items, ...$args);
    }
}
