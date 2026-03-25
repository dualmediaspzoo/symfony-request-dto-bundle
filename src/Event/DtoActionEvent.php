<?php

namespace DualMedia\DtoRequestBundle\Event;

use DualMedia\DtoRequestBundle\Interface\Attribute\HttpActionInterface;
use DualMedia\DtoRequestBundle\Interface\DtoInterface;
use DualMedia\DtoRequestBundle\Traits\Event\ResponseAwareTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Dispatched when an argument is processed, is not valid, and there is a HttpAction set.
 */
class DtoActionEvent extends Event
{
    use ResponseAwareTrait;

    public function __construct(
        private readonly HttpActionInterface $action,
        private readonly DtoInterface $dto,
        private readonly Request $request,
        private readonly int $requestType
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

    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * @see HttpKernelInterface::MAIN_REQUEST
     * @see HttpKernelInterface::SUB_REQUEST
     */
    public function getRequestType(): int
    {
        return $this->requestType;
    }
}
