<?php

namespace DualMedia\DtoRequestBundle\Interfaces\Entity;

interface LabelProcessorServiceInterface
{
    public function getProcessor(
        string $service
    ): LabelProcessorInterface;
}
