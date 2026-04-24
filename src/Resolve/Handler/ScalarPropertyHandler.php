<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Resolve\Handler;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Metadata\Enum\BagEnum;
use DualMedia\DtoRequestBundle\Metadata\Model\Dto;
use DualMedia\DtoRequestBundle\Metadata\Model\Property;
use DualMedia\DtoRequestBundle\Resolve\BagAccessor;
use DualMedia\DtoRequestBundle\Resolve\Model\PendingValue;
use DualMedia\DtoRequestBundle\Resolve\PropertyResolver;
use DualMedia\DtoRequestBundle\Util;

class ScalarPropertyHandler implements FieldHandlerInterface
{
    public function __construct(
        private readonly PropertyResolver $propertyResolver
    ) {
    }

    #[\Override]
    public function supports(
        Property|Dto $meta
    ): bool {
        return $meta instanceof Property;
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

        $resolved = $this->propertyResolver->resolve($meta, $accessor, $defaultBag, $prefix);

        if (null === $resolved) {
            return false;
        }

        $validationPath = Util::buildValidationPath([...$prefix, ...$meta->getRealPathSegments()]);

        // collect phases innermost-first (inner validates before outer)
        $phases = [];
        $current = $resolved->coercion;

        while (null !== $current) {
            if (!empty($current->constraints)) {
                array_unshift($phases, [$current->coerce, $current->constraints]);
            }

            $current = $current->inner;
        }

        $pending[] = new PendingValue(
            $dto,
            $name,
            $resolved->raw,
            $phases,
            $validationPath
        );

        return true;
    }
}
