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
        // A `#[Bag]` on the FindBy parent property scopes its virtual fields'
        // input bag (e.g. routing them to attributes); inherit it when the
        // virtual itself didn't declare one.
        $virtualDefaultBag = $meta->bag ?? $defaultBag;
        $anyInputVisited = false;
        $hasInputBoundVirtuals = false;
        $missingInputBoundCount = 0;

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

                continue;
            }

            $hasInputBoundVirtuals = true;
            $resolved = $this->propertyResolver->resolve($virtualMeta, $accessor, $virtualDefaultBag, $prefix);

            if (null !== $resolved) {
                $raw = $resolved->raw;
                $coercion = $resolved->coercion;
                $dto->visit($name, $target);
                $anyInputVisited = true;
            } else {
                ++$missingInputBoundCount;
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

        // If any input-bound virtual didn't receive its input, short-circuit the
        // loader to the type-appropriate empty value. Running it with partial /
        // null criteria would crash strict DB drivers (e.g. Doctrine on a `null`
        // identifier) without telling the user anything useful. Constraints on
        // the virtual still validate as before — explicit NotNull is what the
        // user uses to surface a per-field violation; absent that, the entity
        // simply stays at its default and a property-level constraint (e.g.
        // NotNull on the property) reports the missing entity.
        // DTOs whose criteria come entirely from Literal/Dynamic virtuals
        // (no input-bound fields at all) load unconditionally.
        if ($hasInputBoundVirtuals && $missingInputBoundCount > 0) {
            $many = $find->many;
            $loader = static fn (): array|null => $many ? [] : null;
        }

        $pending[] = new PendingEntityValue(
            $dto,
            $name,
            $fields,
            $loader,
            Util::buildValidationPath([...$prefix, ...$meta->getRealPathSegments()])
        );

        return $anyInputVisited;
    }
}
