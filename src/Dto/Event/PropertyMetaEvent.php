<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Dto\Event;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Metadata\Model\Dto;
use DualMedia\DtoRequestBundle\Metadata\Model\Property;
use Symfony\Contracts\EventDispatcher\Event;

class PropertyMetaEvent extends Event
{
    public function __construct(
        public readonly AbstractDto $dto,
        public readonly string $path,
        public readonly Property|Dto $meta
    ) {
    }
}
