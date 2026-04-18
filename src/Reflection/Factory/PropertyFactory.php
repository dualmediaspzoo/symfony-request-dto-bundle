<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Reflection\Factory;

use DualMedia\DtoRequestBundle\Coercer\SupportValidator;
use DualMedia\DtoRequestBundle\Dto\Model\Dynamic;
use DualMedia\DtoRequestBundle\Dto\Model\Literal;
use DualMedia\DtoRequestBundle\Metadata\Enum\BagEnum;
use DualMedia\DtoRequestBundle\Metadata\Model\Property;
use Symfony\Component\TypeInfo\Type;
use Symfony\Component\Validator\Constraint;

class PropertyFactory
{
    public function __construct(
        private readonly SupportValidator $validator
    ) {
    }

    /**
     * @param list<Constraint> $constraints
     * @param array<string, Property|Dynamic|Literal> $virtual
     * @param list<object> $meta
     */
    public function create(
        string $name,
        Type $type,
        BagEnum|null $bag = null,
        string|null $path = null,
        array $constraints = [],
        array $virtual = [],
        array $meta = [],
        string|null $objectProviderServiceId = null,
        string|null $description = null
    ): Property {
        return new Property(
            name: $name,
            type: $type,
            bag: $bag,
            path: $path,
            coercer: $this->validator->supports($type),
            constraints: $constraints,
            virtual: $virtual,
            meta: $meta,
            objectProviderServiceId: $objectProviderServiceId,
            description: $description
        );
    }
}
