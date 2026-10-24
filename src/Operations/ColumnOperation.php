<?php

namespace Iterators\Operations;

final class ColumnOperation extends AbstractNativeArrayOperation
{
    protected function apply(array $items, mixed ...$args): array
    {
        return \array_column($items, ...$args);
    }
}
