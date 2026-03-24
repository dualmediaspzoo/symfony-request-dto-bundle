<?php

namespace DualMedia\DtoRequestBundle\Event;

use DualMedia\DtoRequestBundle\Interface\DtoInterface;
use DualMedia\DtoRequestBundle\Traits\Event\ResponseAwareTrait;
use Symfony\Contracts\EventDispatcher\Event;

class DtoInvalidEvent extends Event
{
    use ResponseAwareTrait;

    public function __construct(
        private readonly DtoInterface $dto
    ) {
    }

    public function getDto(): DtoInterface
    {
        return $this->dto;
    }
}
