<?php

namespace DM\DtoRequestBundle\Tests\Model;

class ArrayIterator implements \IteratorAggregate
{
    private array $data;

    public function __construct(
        array $data
    ) {
        $this->data = $data;
    }

    public function getIterator(): \Traversable
    {
        yield from $this->data;
    }
}
