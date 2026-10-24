<?php

namespace Iterators;

interface IteratorOperation
{
    /**
     * Applies this operation lazily to an iterable source.
     *
     * @param iterable $items The input iterable.
     * @param mixed ...$args Operation-specific arguments.
     */
    public function __invoke(iterable $items, mixed ...$args): iterable;
}
