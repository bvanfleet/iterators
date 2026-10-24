<?php

namespace Iterators\Operations;

final class ReplaceRecursiveOperation extends AbstractNativeArrayOperation
{
    protected function apply(array $items, mixed ...$args): array
    {
        return \array_replace_recursive($items, ...$args);
    }
}
