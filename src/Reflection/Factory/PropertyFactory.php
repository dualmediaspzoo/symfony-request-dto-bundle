<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Reflection\Factory;

use DualMedia\DtoRequestBundle\Coercer\SupportValidator;
use DualMedia\DtoRequestBundle\Dto\Model\Dynamic;
use DualMedia\DtoRequestBundle\Dto\Model\Literal;
use DualMedia\DtoRequestBundle\Metadata\Enum\BagEnum;
use DualMedia\DtoRequestBundle\Metadata\Model\Property;
use DualMedia\DtoRequestBundle\Metadata\Model\Type;
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
        array $meta = []
    ): Property {
        return new Property(
            $name,
            $type,
            $bag,
            $path,
            $this->validator->supports($type),
            $constraints,
            $virtual,
            $meta
        );
    }
}