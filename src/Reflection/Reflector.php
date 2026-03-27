<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Reflection;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Metadata\Model\Property;

class Reflector
{
    /**
     * @param class-string<AbstractDto> $class
     *
     * @return array<class-string<AbstractDto>, Property>
     */
    public function reflect(
        string $class
    ): array {
        $reflection = new \ReflectionClass($class);

        $results = [];

        foreach ($reflection->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {

        }
    }
}
