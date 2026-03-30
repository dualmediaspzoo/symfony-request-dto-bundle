<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Resolve;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Metadata\Enum\BagEnum;
use DualMedia\DtoRequestBundle\Metadata\Model\Dto;
use DualMedia\DtoRequestBundle\Reflection\CacheReflector;
use DualMedia\DtoRequestBundle\Resolve\Model\PendingValue;
use DualMedia\DtoRequestBundle\Util;
use Symfony\Component\Validator\Constraints\Type;

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
        BagAccessor $accessor,
        BagEnum $defaultBag,
        array $prefix = [],
        array &$pending = []
    ): bool {
        if (null === ($metadata = $this->cacheReflector->get($dto::class))) {
            return false;
        }

        $anyVisited = false;

        foreach ($metadata->fields as $name => $meta) {
            if ($meta instanceof Dto) {
                assert(null !== $meta->type->fqcn && is_subclass_of($meta->type->fqcn, AbstractDto::class));

                $childBag = $meta->bag ?? $defaultBag;
                $childPath = $meta->getRealPath();
                $childSegments = [...$prefix, $childPath];

                if ($meta->type->isCollection()) {
                    /** @var class-string<AbstractDto> $childFqcn */
                    $childFqcn = $meta->type->fqcn;

                    $visited = $this->extractCollection(
                        $dto,
                        $name,
                        $childFqcn,
                        $meta->type->collection,
                        $accessor,
                        $childBag,
                        $childSegments,
                        $pending
                    );
                } else {
                    $visited = $this->extractSingleDto(
                        $dto,
                        $name,
                        $meta,
                        $accessor,
                        $childBag,
                        $childSegments,
                        $pending
                    );
                }

                if ($visited) {
                    $dto->visit($name);
                    $anyVisited = true;
                }

                continue;
            }

            // check if this is a DTO collection (array of DTOs comes through as Property)
            if ($meta->type->isCollection()
                && null !== $meta->type->fqcn
                && is_subclass_of($meta->type->fqcn, AbstractDto::class)) {
                $visited = $this->extractCollection(
                    $dto,
                    $name,
                    $meta->type->fqcn,
                    $meta->type->collection,
                    $accessor,
                    $meta->bag ?? $defaultBag,
                    [...$prefix, $meta->getRealPath()],
                    $pending
                );

                if ($visited) {
                    $dto->visit($name);
                    $anyVisited = true;
                }

                continue;
            }

            $result = $this->propertyResolver->resolve($meta, $accessor, $defaultBag, $prefix);

            if (null === $result) {
                continue;
            }

            $dto->visit($name);
            $anyVisited = true;

            $pending[] = new PendingValue(
                $dto,
                $name,
                $result->value,
                $result->constraints,
                Util::buildValidationPath([...$prefix, $meta->getRealPath()])
            );
        }

        return $anyVisited;
    }

    /**
     * @param list<string> $childSegments
     * @param list<PendingValue> $pending
     */
    private function extractSingleDto(
        AbstractDto $dto,
        string $name,
        Dto $meta,
        BagAccessor $accessor,
        BagEnum $childBag,
        array $childSegments,
        array &$pending
    ): bool {
        /** @var class-string<AbstractDto> $fqcn */
        $fqcn = $meta->type->fqcn;

        $child = new $fqcn();
        $child->setParentDto($dto);
        $dto->{$name} = $child;

        return $this->extract(
            $child,
            $accessor,
            $childBag,
            $childSegments,
            $pending
        );
    }

    /**
     * @param class-string<AbstractDto> $fqcn
     * @param list<string> $childSegments
     * @param list<PendingValue> $pending
     */
    private function extractCollection(
        AbstractDto $dto,
        string $name,
        string $fqcn,
        string|null $collectionType,
        BagAccessor $accessor,
        BagEnum $childBag,
        array $childSegments,
        array &$pending
    ): bool {
        $children = Collection::class === $collectionType
            ? new ArrayCollection()
            : [];

        $dto->{$name} = $children;

        $raw = $accessor->get($childBag, $childSegments);

        if (null === $raw) {
            return false;
        }

        // type mismatch: expected array but got something else
        if (!is_array($raw)) {
            $pending[] = new PendingValue(
                $dto,
                $name,
                $raw,
                [new Type(type: 'array')],
                Util::buildValidationPath($childSegments)
            );

            return true;
        }

        foreach ($raw as $index => $entry) {
            $child = new $fqcn();
            $child->setParentDto($dto);

            $this->extract(
                $child,
                $accessor,
                $childBag,
                [...$childSegments, (string)$index],
                $pending
            );

            $children[] = $child;
        }

        $dto->{$name} = $children;

        return true;
    }
}
