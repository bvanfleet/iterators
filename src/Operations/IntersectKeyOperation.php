<?php

namespace Iterators\Operations;

final class IntersectKeyOperation extends AbstractNativeArrayOperation
{
    protected function apply(array $items, mixed ...$args): array
    {
        return \array_intersect_key($items, ...$args);
    }
}
