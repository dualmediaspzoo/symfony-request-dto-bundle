<?php

namespace DM\DtoRequestBundle\Annotations\Dto;

use DM\DtoRequestBundle\Interfaces\Attribute\DtoAnnotationInterface;

/**
 * @Annotation
 *
 * This class should be put on enums when you want the enum to be created by looking at the const (key) name, not the value
 */
class FromKey implements DtoAnnotationInterface
{
}
