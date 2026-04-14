<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Resolve\Handler;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Metadata\Enum\BagEnum;
use DualMedia\DtoRequestBundle\Metadata\Model\Dto;
use DualMedia\DtoRequestBundle\Metadata\Model\Property;
use DualMedia\DtoRequestBundle\Reflection\CacheReflector;
use DualMedia\DtoRequestBundle\Resolve\BagAccessor;
use DualMedia\DtoRequestBundle\Resolve\Extractor;
use DualMedia\DtoRequestBundle\Type\TypeInfoUtils;

class SingleDtoHandler implements FieldHandlerInterface
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
        return $meta instanceof Dto && !TypeInfoUtils::isCollection($meta->type);
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
        assert($meta instanceof Dto);

        /** @var class-string<AbstractDto> $fqcn */
        $fqcn = TypeInfoUtils::getClassName($meta->type);

        $childMetadata = $this->cacheReflector->get($fqcn);

        if (null === $childMetadata) {
            return false;
        }

        $child = new $fqcn();
        $child->setParentDto($dto);
        $dto->{$name} = $child;

        $childPrefix = '' !== ($realPath = $meta->getRealPath())
            ? [...$prefix, $realPath]
            : $prefix;

        return $this->extractor->extract(
            $childMetadata,
            $child,
            $accessor,
            $meta->bag ?? $defaultBag,
            $childPrefix,
            $pending
        );
    }
}
