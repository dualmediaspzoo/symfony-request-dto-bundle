<?php

namespace DualMedia\DtoRequestBundle\Event;

use DualMedia\DtoRequestBundle\Interfaces\DtoInterface;
use DualMedia\DtoRequestBundle\ValueResolver\DtoValueResolver;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * When the DTO objects are resolved by {@link DtoValueResolver} this event is fired.
 */
class DtoResolvedEvent extends Event
{
    public function __construct(
        private readonly DtoInterface $dto
    ) {
    }

    public function getDto(): DtoInterface
    {
        return $this->dto;
    }
}
