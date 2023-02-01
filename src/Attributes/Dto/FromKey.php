<?php

namespace DualMedia\DtoRequestBundle\Attributes\Dto;

use DualMedia\DtoRequestBundle\Interfaces\Attribute\DtoAttributeInterface;
use DualMedia\DtoRequestBundle\Interfaces\Entity\LabelProcessorInterface;
use DualMedia\DtoRequestBundle\Service\Entity\LabelProcessor\DefaultProcessor;

/**
 * This class should be put on enums when you want the enum to be created by looking at the const (key) name, not the value
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FromKey implements DtoAttributeInterface
{
    /**
     * @param class-string<LabelProcessorInterface>|string $normalizer service id for normalizer
     */
    public function __construct(
        public readonly string $normalizer = DefaultProcessor::class
    ) {
    }
}
