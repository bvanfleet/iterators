<?php

namespace Iterators\Consumers;

use Iterators\IteratorConsumer;

class CountValuesConsumer implements IteratorConsumer
{
    public function __invoke(iterable $items, mixed ...$args): mixed
    {
        $counts = [];

        foreach ($items as $item) {
            if (!is_int($item) && !is_string($item)) {
                array_count_values([$item]);
                continue;
            }

            $counts[$item] = ($counts[$item] ?? 0) + 1;
        }

        return $counts;
    }
}
