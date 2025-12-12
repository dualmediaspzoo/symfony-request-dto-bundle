<?php

namespace DualMedia\DtoRequestBundle\Service\Entity\LabelProcessor;

use DualMedia\DtoRequestBundle\Interfaces\Entity\LabelProcessorInterface;

class DefaultProcessor implements LabelProcessorInterface
{
    #[\Override]
    public function normalize(
        string $value
    ): string {
        return $value;
    }

    #[\Override]
    public function denormalize(
        string $value
    ): string {
        return $value;
    }
}
