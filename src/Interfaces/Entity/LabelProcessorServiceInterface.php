<?php

namespace DualMedia\DtoRequestBundle\Interfaces\Entity;

interface LabelProcessorServiceInterface
{
    /**
     * @param string $service
     * @return LabelProcessorInterface
     */
    public function getProcessor(
        string $service
    ): LabelProcessorInterface;
}
