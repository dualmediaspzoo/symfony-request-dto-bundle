<?php

namespace DualMedia\DtoRequestBundle\Event;

use DualMedia\DtoRequestBundle\Interface\DtoInterface;
use DualMedia\DtoRequestBundle\Traits\Event\ResponseAwareTrait;
use Symfony\Contracts\EventDispatcher\Event;

class DtoInvalidEvent extends Event
{
    use ResponseAwareTrait;

    /**
     * @param list<DtoInterface> $objects
     */
    public function __construct(
        private readonly array $objects
    ) {
    }

    /**
     * @return list<DtoInterface>
     */
    public function getObjects(): array
    {
        return $this->objects;
    }
}
