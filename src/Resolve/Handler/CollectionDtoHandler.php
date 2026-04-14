<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Resolve\Handler;

use Doctrine\Common\Collections\ArrayCollection;
use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Metadata\Enum\BagEnum;
use DualMedia\DtoRequestBundle\Metadata\Model\Dto;
use DualMedia\DtoRequestBundle\Metadata\Model\Property;
use DualMedia\DtoRequestBundle\Reflection\CacheReflector;
use DualMedia\DtoRequestBundle\Resolve\BagAccessor;
use DualMedia\DtoRequestBundle\Resolve\Extractor;
use DualMedia\DtoRequestBundle\Resolve\Model\PendingValue;
use DualMedia\DtoRequestBundle\Type\TypeInfoUtils;
use DualMedia\DtoRequestBundle\Util;
use Symfony\Component\Validator\Constraints\Type;

class CollectionDtoHandler implements FieldHandlerInterface
{
    public function __construct(
        private readonly Extractor $extractor,
        private readonly CacheReflector $cacheReflector
    ) {
    }

    #[\Override]
    public function supports(
        Property|Dto $meta
    ): bool {
        $className = TypeInfoUtils::getCollectionValueClassName($meta->type);

        return null !== $className && is_subclass_of($className, AbstractDto::class);
    }

    #[\Override]
    public function handle(
        AbstractDto $dto,
        string $name,
        Property|Dto $meta,
        BagAccessor $accessor,
        BagEnum $defaultBag,
        array $prefix,
        array &$pending
    ): bool {
        /** @var class-string<AbstractDto> $fqcn */
        $fqcn = TypeInfoUtils::getCollectionValueClassName($meta->type);
        $childBag = $meta->bag ?? $defaultBag;
        $childSegments = '' !== ($realPath = $meta->getRealPath())
            ? [...$prefix, $realPath]
            : $prefix;

        $children = TypeInfoUtils::isDoctrineCollection($meta->type)
            ? new ArrayCollection()
            : [];

        $dto->{$name} = $children;

        $raw = $accessor->get($childBag, $childSegments);

        if (null === $raw) {
            return false;
        }

        if (!is_array($raw)) {
            $pending[] = new PendingValue(
                $dto,
                $name,
                $raw,
                [[static fn (mixed $v): mixed => $v, [new Type(type: 'array')]]],
                Util::buildValidationPath($childSegments)
            );

            return true;
        }

        $childMetadata = $this->cacheReflector->get($fqcn);

        if (null === $childMetadata) {
            return false;
        }

        foreach ($raw as $index => $entry) {
            $child = new $fqcn();
            $child->setParentDto($dto);

            $this->extractor->extract(
                $childMetadata,
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
