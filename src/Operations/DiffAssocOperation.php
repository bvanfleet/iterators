<?php

namespace Iterators\Operations;

final class DiffAssocOperation extends AbstractNativeArrayOperation
{
    protected function apply(array $items, mixed ...$args): array
    {
        return \array_diff_assoc($items, ...$args);
    }
}
