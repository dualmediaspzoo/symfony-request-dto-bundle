<?php

namespace DualMedia\DtoRequestBundle\Event;

use DualMedia\DtoRequestBundle\Interface\DtoInterface;
use DualMedia\DtoRequestBundle\Traits\Event\ResponseAwareTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\Event;

class DtoInvalidEvent extends Event
{
    use ResponseAwareTrait;

    /**
     * @param list<DtoInterface> $objects
     */
    public function __construct(
        private readonly array $objects,
        private readonly Request $request,
        private readonly int $requestType
    ) {
    }

    /**
     * @return list<DtoInterface>
     */
    public function getObjects(): array
    {
        return $this->objects;
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
