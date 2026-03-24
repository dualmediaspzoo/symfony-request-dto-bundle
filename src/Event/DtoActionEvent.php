<?php

namespace DualMedia\DtoRequestBundle\Event;

use DualMedia\DtoRequestBundle\Interface\Attribute\HttpActionInterface;
use DualMedia\DtoRequestBundle\Interface\DtoInterface;
use DualMedia\DtoRequestBundle\Traits\Event\ResponseAwareTrait;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Dispatched when an argument is processed, is not valid, and there is a HttpAction set.
 */
class DtoActionEvent extends Event
{
    use ResponseAwareTrait;

    public function __construct(
        private readonly HttpActionInterface $action,
        private readonly DtoInterface $dto
    ) {
    }

    public function getAction(): HttpActionInterface
    {
        return $this->action;
    }

    public function getDto(): DtoInterface
    {
        return $this->dto;
    }
}
