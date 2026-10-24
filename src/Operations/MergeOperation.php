<?php

namespace Iterators\Operations;

final class MergeOperation extends AbstractNativeArrayOperation
{
    protected function apply(array $items, mixed ...$args): array
    {
        return \array_merge($items, ...$args);
    }
}
