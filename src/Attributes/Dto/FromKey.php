<?php

namespace DualMedia\DtoRequestBundle\Attributes\Dto;

use DualMedia\DtoRequestBundle\Interfaces\Attribute\DtoAttributeInterface;

/**
 * This class should be put on enums when you want the enum to be created by looking at the const (key) name, not the value
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FromKey implements DtoAttributeInterface
{
}
