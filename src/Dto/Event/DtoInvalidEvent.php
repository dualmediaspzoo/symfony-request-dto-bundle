<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Dto\Event;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\Event;

class DtoInvalidEvent extends Event
{
    use ResponseAwareTrait;

    /**
     * @param list<AbstractDto> $objects
     */
    public function __construct(
        private readonly array $objects,
        private readonly Request $request,
        private readonly int $requestType
    ) {
    }

    /**
     * @return list<AbstractDto>
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
