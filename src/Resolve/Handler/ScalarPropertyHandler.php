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
        array &$pending
    ): bool {
        assert($meta instanceof Property);

        $result = $this->propertyResolver->resolve($meta, $accessor, $defaultBag, $prefix);

        if (null === $result) {
            return false;
        }

        $pending[] = new PendingValue(
            $dto,
            $name,
            $result->value,
            $result->constraints,
            Util::buildValidationPath([...$prefix, $meta->getRealPath()])
        );

        return true;
    }
}
