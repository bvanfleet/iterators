<?php

namespace Iterators\Operations;

final class IntersectOperation extends AbstractNativeArrayOperation
{
    protected function apply(array $items, mixed ...$args): array
    {
        return \array_intersect($items, ...$args);
    }
}
