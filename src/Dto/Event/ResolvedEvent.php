<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Dto\Event;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use Symfony\Contracts\EventDispatcher\Event;

class ResolvedEvent extends Event
{
    public function __construct(
        private readonly AbstractDto $dto
    ) {
    }

    public function getDto(): AbstractDto
    {
        return $this->dto;
    }
}
