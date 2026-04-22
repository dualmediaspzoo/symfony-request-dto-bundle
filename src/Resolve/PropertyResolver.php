<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Resolve;

use DualMedia\DtoRequestBundle\Coercer\Registry;
use DualMedia\DtoRequestBundle\Metadata\Enum\BagEnum;
use DualMedia\DtoRequestBundle\Metadata\Model\Property;
use DualMedia\DtoRequestBundle\Resolve\Model\ResolvedValue;

class PropertyResolver
{
    public function __construct(
        private readonly Registry $coercerRegistry
    ) {
    }

    /**
     * Extracts a raw value from the request and builds its coercion pipeline.
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
    ): ResolvedValue|null {
        $bag = $property->bag ?? $defaultBag;
        $segments = [...$prefix, $property->getRealPath()];

        if (!$accessor->has($bag, $segments)) {
            return null;
        }

        $raw = $accessor->get($bag, $segments);

        if (null === $property->coercer) {
            return new ResolvedValue($raw);
        }

        return new ResolvedValue(
            $raw,
            $this->coercerRegistry->get($property->coercer)
                ->coerce($property)
        );
    }
}
