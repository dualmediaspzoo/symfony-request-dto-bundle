<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Dto\Event;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Metadata\Model\Action;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\Event;

class ActionEvent extends Event
{
    use ResponseAwareTrait;

    public function __construct(
        private readonly AbstractDto $dto,
        private readonly string $property,
        private readonly mixed $value,
        private readonly Action $action,
        private readonly Request $request,
        private readonly int $requestType
    ) {
    }

    public function getDto(): AbstractDto
    {
        return $this->dto;
    }

    public function getProperty(): string
    {
        return $this->property;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function getAction(): Action
    {
        return $this->action;
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
