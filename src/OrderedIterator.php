<?php

namespace Iterators;

class OrderedIterator implements \IteratorAggregate
{
    private array $items;

    private function __construct(iterable $source)
    {
        $this->items = [];
        foreach ($source as $k => $v) {
            $this->items[$k] = $v;
        }
    }

    public static function from(iterable $source): self
    {
        return new self($source);
    }

    public function getIterator(): \Traversable
    {
        yield from $this->items;
    }

    public function sort(?callable $cmp = null): self
    {
        if ($cmp !== null) {
            uasort($this->items, $cmp);
        } else {
            asort($this->items);
        }

        return $this;
    }

    public function reverse(): self
    {
        $this->items = array_reverse($this->items, true);

        return $this;
    }
}
