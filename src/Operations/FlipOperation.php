<?php

namespace Iterators\Operations;

final class FlipOperation extends AbstractNativeArrayOperation
{
    protected function apply(array $items, mixed ...$args): array
    {
        return \array_flip($items, ...$args);
    }
}
