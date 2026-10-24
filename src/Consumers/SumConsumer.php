<?php

namespace Iterators\Consumers;

use \Iterators\IteratorConsumer;

class SumConsumer implements IteratorConsumer
{
    public function __invoke(iterable $items, mixed ...$args): mixed
    {
        $sum = 0;
        foreach ($items as $item) {
            $sum += $item;
        }
        
        return $sum;
    }
}
