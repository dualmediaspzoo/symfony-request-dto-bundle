<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Resolve;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Metadata\Enum\BagEnum;
use DualMedia\DtoRequestBundle\Metadata\Model\Dto;
use DualMedia\DtoRequestBundle\Reflection\CacheReflector;
use DualMedia\DtoRequestBundle\Resolve\Model\PendingValue;
use Symfony\Component\HttpFoundation\Request;

class Extractor
{
    public function __construct(
        private readonly PropertyResolver $propertyResolver,
        private readonly CacheReflector $cacheReflector
    ) {
    }

    /**
     * Recursively walks the metadata tree, extracting and coercing values
     * into PendingValue entries without validating.
     *
     * @param list<string> $prefix path segments from parent DTOs
     * @param list<PendingValue> $pending collected entries (mutated)
     */
    public function extract(
        AbstractDto $dto,
        Request $request,
        BagEnum $defaultBag,
        array $prefix = [],
        array &$pending = []
    ): void {
        $metadata = $this->cacheReflector->get($dto::class) ?? [];
        $pathPrefix = [] !== $prefix ? implode('.', $prefix).'.' : '';

        foreach ($metadata as $name => $meta) {
            if ($meta instanceof Dto) {
                assert(null !== $meta->type->fqcn && is_subclass_of($meta->type->fqcn, AbstractDto::class));

                $child = new ($meta->type->fqcn)();
                $child->setParentDto($dto);
                $dto->{$name} = $child;

                $this->extract(
                    $child,
                    $request,
                    $meta->bag ?? $defaultBag,
                    [...$prefix, $meta->path ?? $name],
                    $pending
                );

                continue;
            }

            $result = $this->propertyResolver->resolve($meta, $request, $defaultBag, $prefix);

            if (null === $result) {
                continue;
            }

            $dto->visit($name);

            $pending[] = new PendingValue(
                $dto,
                $name,
                $result->value,
                $result->constraints,
                $pathPrefix.$name
            );
        }
    }
}