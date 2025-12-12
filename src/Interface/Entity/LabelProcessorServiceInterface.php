<?php

namespace DualMedia\DtoRequestBundle\Interface\Entity;

interface LabelProcessorServiceInterface
{
    public function getProcessor(
        string $service
    ): LabelProcessorInterface;
}
