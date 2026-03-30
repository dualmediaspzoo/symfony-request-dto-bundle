<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Resolve;

use DualMedia\DtoRequestBundle\Coercer\Model\Result;
use DualMedia\DtoRequestBundle\Coercer\Registry;
use DualMedia\DtoRequestBundle\Metadata\Enum\BagEnum;
use DualMedia\DtoRequestBundle\Metadata\Model\Property;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

class PropertyResolver
{
    private const string MISSING = '__dto_missing__';

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
        Request $request,
        BagEnum $defaultBag,
        array $prefix = []
    ): Result|null {
        $bag = $this->getBag($request, $property->bag ?? $defaultBag);
        $raw = $this->extract($bag, $property->getRealPath(), $prefix);

        if (self::MISSING === $raw) {
            return null;
        }

        if (null === $property->coercer) {
            return new Result($raw);
        }

        return $this->coercerRegistry->get($property->coercer)
            ->coerce($property, $raw);
    }

    private function getBag(
        Request $request,
        BagEnum $bag
    ): ParameterBag|HeaderBag {
        return match ($bag) {
            BagEnum::Query => $request->query,
            BagEnum::Request => $request->request,
            BagEnum::Attributes => $request->attributes,
            BagEnum::Cookies => $request->cookies,
            BagEnum::Headers => $request->headers,
            BagEnum::Files => $request->files,
        };
    }

    /**
     * Walks into nested bag data using the prefix segments,
     * then reads the property path from the result.
     *
     * @param list<string> $prefix
     */
    private function extract(
        ParameterBag|HeaderBag $bag,
        string $path,
        array $prefix
    ): mixed {
        if ([] === $prefix) {
            if (!$bag->has($path)) {
                return self::MISSING;
            }

            return $bag->get($path);
        }

        // nested: get the root key as an array, then walk the remaining segments
        $root = array_shift($prefix);

        if (!$bag->has($root)) {
            return self::MISSING;
        }

        $data = $bag->all($root);

        foreach ($prefix as $segment) {
            if (!array_key_exists($segment, $data) || !is_array($data[$segment])) {
                return self::MISSING;
            }

            $data = $data[$segment];
        }

        if (!array_key_exists($path, $data)) {
            return self::MISSING;
        }

        return $data[$path];
    }
}
