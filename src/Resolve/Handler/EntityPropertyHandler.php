<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Resolve\Handler;

use DualMedia\DtoRequestBundle\Coercer\Registry;
use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Metadata\Enum\BagEnum;
use DualMedia\DtoRequestBundle\Metadata\Model\Dto;
use DualMedia\DtoRequestBundle\Metadata\Model\FindBy;
use DualMedia\DtoRequestBundle\Metadata\Model\Property;
use DualMedia\DtoRequestBundle\MetadataUtils;
use DualMedia\DtoRequestBundle\Provider\EntityProviderRegistry;
use DualMedia\DtoRequestBundle\Resolve\BagAccessor;
use DualMedia\DtoRequestBundle\Resolve\Model\PendingEntityValue;
use DualMedia\DtoRequestBundle\Resolve\Model\PendingValue;
use DualMedia\DtoRequestBundle\Resolve\PropertyResolver;
use DualMedia\DtoRequestBundle\Type\TypeInfoUtils;
use DualMedia\DtoRequestBundle\Util;

class EntityPropertyHandler implements FieldHandlerInterface
{
    public function __construct(
        private readonly PropertyResolver $propertyResolver,
        private readonly EntityProviderRegistry $entityProviderRegistry,
        private readonly Registry $coercerRegistry
    ) {
    }

    #[\Override]
    public function supports(
        Property|Dto $meta
    ): bool {
        return $meta instanceof Property && MetadataUtils::exists(FindBy::class, $meta->meta);
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
        assert($meta instanceof Property);

        if ([] === $meta->virtual) {
            return false;
        }

        $find = MetadataUtils::single(FindBy::class, $meta->meta);
        assert(null !== $find);

        $entityClass = $find->many
            ? TypeInfoUtils::getCollectionValueClassName($meta->type)
            : TypeInfoUtils::getClassName($meta->type);

        assert(null !== $entityClass);
        /** @var class-string $entityClass */
        $fields = [];

        foreach ($meta->virtual as $target => $virtualMeta) {
            if (!$virtualMeta instanceof Property) {
                continue;
            }

            $resolved = $this->propertyResolver->resolve($virtualMeta, $accessor, $defaultBag, $prefix);

            if (null !== $resolved) {
                $raw = $resolved->raw;
                $coercion = $resolved->coercion;
            } else {
                $raw = TypeInfoUtils::isCollection($virtualMeta->type) ? [] : null;
                $coercion = null !== $virtualMeta->coercer
                    ? $this->coercerRegistry->get($virtualMeta->coercer)->coerce($virtualMeta)
                    : null;
            }

            $phases = [];
            $current = $coercion;

            while (null !== $current) {
                if (!empty($current->constraints)) {
                    array_unshift($phases, [$current->coerce, $current->constraints]);
                }

                $current = $current->inner;
            }

            if (!empty($virtualMeta->constraints)) {
                $phases[] = [static fn (mixed $v): mixed => $v, $virtualMeta->constraints];
            }

            $validationPath = Util::buildValidationPath([...$prefix, $meta->getRealPath(), $virtualMeta->getRealPath()]);

            $fields[$target] = new PendingValue(
                $dto,
                $name,
                $raw,
                $phases,
                $validationPath
            );

            $dto->visit($name, $target);
        }

        if ([] === $fields) {
            return false;
        }

        $registry = $this->entityProviderRegistry;
        /** @var list<\DualMedia\DtoRequestBundle\Metadata\Model\FindBy|\DualMedia\DtoRequestBundle\Metadata\Model\Limit|\DualMedia\DtoRequestBundle\Metadata\Model\Offset|\DualMedia\DtoRequestBundle\Metadata\Model\AsDoctrineReference> $metaList */
        $metaList = $meta->meta;

        $pending[] = new PendingEntityValue(
            $dto,
            $name,
            $fields,
            static function (array $criteria) use ($registry, $entityClass, $metaList): mixed {
                return $registry->get($entityClass)->find($criteria, $metaList);
            },
            Util::buildValidationPath([...$prefix, $meta->getRealPath()])
        );

        return true;
    }
}
