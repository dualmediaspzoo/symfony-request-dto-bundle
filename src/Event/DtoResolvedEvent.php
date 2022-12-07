<?php

namespace DM\DtoRequestBundle\Event;

use DM\DtoRequestBundle\ArgumentResolver\DtoArgumentResolver;
use DM\DtoRequestBundle\Interfaces\DtoInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * When the DTO objects are resolved by {@link DtoArgumentResolver} this event is fired
 */
class DtoResolvedEvent extends Event
{
    private DtoInterface $dto;

    public function __construct(
        DtoInterface $dto
    ) {
        $this->dto = $dto;
    }

    public function getDto(): DtoInterface
    {
        return $this->dto;
    }
}
