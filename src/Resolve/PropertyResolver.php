<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Resolve;

use DualMedia\DtoRequestBundle\Coercer\Model\Result;
use DualMedia\DtoRequestBundle\Coercer\Registry;
use DualMedia\DtoRequestBundle\Metadata\Enum\BagEnum;
use DualMedia\DtoRequestBundle\Metadata\Model\Property;

class PropertyResolver
{
    public function __construct(
        private readonly Registry $coercerRegistry
    ) {
    }

    /**
     * Extracts a raw value from the request and coerces it.
     *
     * Returns null when the input was not found in the request bag.
     *
     * @param list<string> $prefix path segments from parent DTOs
     */
    public function resolve(
        Property $property,
        BagAccessor $accessor,
        BagEnum $defaultBag,
        array $prefix = []
    ): Result|null {
        $bag = $property->bag ?? $defaultBag;
        $segments = [...$prefix, $property->getRealPath()];

        if (!$accessor->has($bag, $segments)) {
            return null;
        }

        $raw = $accessor->get($bag, $segments);

        if (null === $property->coercer) {
            return new Result($raw);
        }

        return $this->coercerRegistry->get($property->coercer)
            ->coerce($property, $raw);
    }
}
