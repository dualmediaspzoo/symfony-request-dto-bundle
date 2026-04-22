<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Metadata\Model;

readonly class LabelProcessor
{
    public function __construct(
        public string $serviceId
    ) {
    }
}
