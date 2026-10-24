<?php

namespace Iterators;

interface IteratorConsumer
{
    public function __invoke(iterable $items, mixed ...$args): mixed;
}
