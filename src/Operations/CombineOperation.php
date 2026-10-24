<?php

namespace Iterators\Operations;

final class CombineOperation extends AbstractNativeArrayOperation
{
    protected function apply(array $items, mixed ...$args): array
    {
        return \array_combine($items, ...$args);
    }
}
