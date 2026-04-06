<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Resolve\Handler;

use Doctrine\Common\Collections\ArrayCollection;
use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Metadata\Enum\BagEnum;
use DualMedia\DtoRequestBundle\Metadata\Model\Dto;
use DualMedia\DtoRequestBundle\Metadata\Model\Property;
use DualMedia\DtoRequestBundle\Resolve\BagAccessor;
use DualMedia\DtoRequestBundle\Resolve\Extractor;
use DualMedia\DtoRequestBundle\Resolve\Model\PendingValue;
use DualMedia\DtoRequestBundle\Resolve\TypeInfoHelper;
use DualMedia\DtoRequestBundle\Util;
use Symfony\Component\Validator\Constraints\Type;

class CollectionDtoHandler implements FieldHandlerInterface
{
    public function __construct(
        private readonly Extractor $extractor
    ) {
    }

    #[\Override]
    public function supports(
        Property|Dto $meta
    ): bool {
        $className = TypeInfoHelper::getCollectionValueClassName($meta->type);

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
        $fqcn = TypeInfoHelper::getCollectionValueClassName($meta->type);
        $childBag = $meta->bag ?? $defaultBag;
        $childSegments = [...$prefix, $meta->getRealPath()];

        $children = TypeInfoHelper::isDoctrineCollection($meta->type)
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
                [new Type(type: 'array')],
                Util::buildValidationPath($childSegments)
            );

            return true;
        }

        foreach ($raw as $index => $entry) {
            $child = new $fqcn();
            $child->setParentDto($dto);

            $this->extractor->extract(
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
