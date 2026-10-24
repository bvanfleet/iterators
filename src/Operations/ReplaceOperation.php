<?php

namespace Iterators\Operations;

final class ReplaceOperation extends AbstractNativeArrayOperation
{
    protected function apply(array $items, mixed ...$args): array
    {
        return \array_replace($items, ...$args);
    }
}
