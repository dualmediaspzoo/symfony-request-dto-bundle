<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Resolve\Handler;

use DualMedia\DtoRequestBundle\Coercer\Registry;
use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Dto\Model\Dynamic;
use DualMedia\DtoRequestBundle\Dto\Model\Literal;
use DualMedia\DtoRequestBundle\Metadata\Enum\BagEnum;
use DualMedia\DtoRequestBundle\Metadata\Model\Dto;
use DualMedia\DtoRequestBundle\Metadata\Model\FindBy;
use DualMedia\DtoRequestBundle\Metadata\Model\Property;
use DualMedia\DtoRequestBundle\Metadata\Model\WithObjectProvider;
use DualMedia\DtoRequestBundle\MetadataUtils;
use DualMedia\DtoRequestBundle\Provider\DynamicParameterRegistry;
use DualMedia\DtoRequestBundle\Provider\EntityProviderRegistry;
use DualMedia\DtoRequestBundle\Provider\Interface\ProviderInterface;
use DualMedia\DtoRequestBundle\Resolve\BagAccessor;
use DualMedia\DtoRequestBundle\Resolve\Model\PendingEntityValue;
use DualMedia\DtoRequestBundle\Resolve\Model\PendingValue;
use DualMedia\DtoRequestBundle\Resolve\PropertyResolver;
use DualMedia\DtoRequestBundle\Type\TypeInfoUtils;
use DualMedia\DtoRequestBundle\Util;
use Symfony\Component\DependencyInjection\ServiceLocator;

/**
 * @phpstan-import-type MetaFindModel from ProviderInterface
 */
class EntityPropertyHandler implements FieldHandlerInterface
{
    /**
     * @param ServiceLocator<ProviderInterface<object>> $objectProviderLocator
     */
    public function __construct(
        private readonly PropertyResolver $propertyResolver,
        private readonly EntityProviderRegistry $entityProviderRegistry,
        private readonly Registry $coercerRegistry,
        private readonly DynamicParameterRegistry $dynamicParameterRegistry,
        private readonly ServiceLocator $objectProviderLocator
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
        array &$pending,
        array &$seen = []
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
            if ($virtualMeta instanceof Dynamic
                || $virtualMeta instanceof Literal) {
                $value = $virtualMeta instanceof Dynamic
                    ? $this->dynamicParameterRegistry->get($virtualMeta->name)
                    : $virtualMeta->value;

                $fields[$target] = new PendingValue(
                    $dto,
                    $name,
                    $value,
                    [],
                    Util::buildValidationPath([...$prefix, $target])
                );

                $dto->visit($name, $target);

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

            $validationPath = Util::buildValidationPath([...$prefix, ...$virtualMeta->getRealPathSegments()]);

            $fields[$target] = new PendingValue(
                $dto,
                $name,
                $raw,
                $phases,
                $validationPath
            );

            $dto->visit($name, $target);
        }

        if (empty($fields)) {
            return false;
        }

        /** @var list<MetaFindModel> $metaList */
        $metaList = $meta->meta;

        if (null !== $meta->objectProviderServiceId) {
            $wop = MetadataUtils::single(WithObjectProvider::class, $meta->meta);
            assert(null !== $wop);

            $provider = $this->objectProviderLocator->get($meta->objectProviderServiceId);
            $closure = $wop->closure;
            $loader = static function (array $criteria) use ($closure, $provider, $metaList): mixed {
                return $closure($provider, $criteria, $metaList);
            };
        } else {
            $loader = function (array $criteria) use ($entityClass, $metaList): mixed {
                return $this->entityProviderRegistry->get($entityClass)->find($criteria, $metaList);
            };
        }

        $pending[] = new PendingEntityValue(
            $dto,
            $name,
            $fields,
            $loader,
            Util::buildValidationPath([...$prefix, ...$meta->getRealPathSegments()])
        );

        return true;
    }
}
