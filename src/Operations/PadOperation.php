<?php

namespace Iterators\Operations;

final class PadOperation extends AbstractNativeArrayOperation
{
    protected function apply(array $items, mixed ...$args): array
    {
        return \array_pad($items, ...$args);
    }
}
