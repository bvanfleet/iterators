<?php

namespace Iterators\Operations;

final class IntersectAssocOperation extends AbstractNativeArrayOperation
{
    protected function apply(array $items, mixed ...$args): array
    {
        return \array_intersect_assoc($items, ...$args);
    }
}
