<?php

namespace Iterators\Operations;

final class ChunkOperation extends AbstractNativeArrayOperation
{
    protected function apply(array $items, mixed ...$args): array
    {
        return \array_chunk($items, ...$args);
    }
}
