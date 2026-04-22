<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Resolve\Handler;

use Doctrine\Common\Collections\ArrayCollection;
use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Metadata\Enum\BagEnum;
use DualMedia\DtoRequestBundle\Metadata\Model\Dto;
use DualMedia\DtoRequestBundle\Metadata\Model\Property;
use DualMedia\DtoRequestBundle\Reflection\Interface\MainDtoMemoizerInterface;
use DualMedia\DtoRequestBundle\Resolve\BagAccessor;
use DualMedia\DtoRequestBundle\Resolve\Interface\ExtractorInterface;
use DualMedia\DtoRequestBundle\Resolve\Model\PendingValue;
use DualMedia\DtoRequestBundle\Type\TypeInfoUtils;
use DualMedia\DtoRequestBundle\Util;
use Symfony\Component\Validator\Constraints\Type;

class CollectionDtoHandler implements FieldHandlerInterface
{
    public function __construct(
        private readonly ExtractorInterface $extractor,
        private readonly MainDtoMemoizerInterface $memoizer
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
        array &$pending,
        array &$seen = []
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

        if (null === ($raw = $accessor->get($childBag, $childSegments))) {
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

        $childMetadata = $this->memoizer->get($fqcn);

        if (null === $childMetadata) {
            return false;
        }

        $descendBag = $childMetadata->defaultBag ?? $childBag;

        foreach ($raw as $index => $entry) {
            $child = new $fqcn();
            $child->setParentDto($dto);

            $this->extractor->extract(
                $childMetadata,
                $child,
                $accessor,
                $descendBag,
                [...$childSegments, (string)$index],
                $pending,
                $seen
            );

            $children[] = $child;
        }

        $dto->{$name} = $children;

        return true;
    }
}
