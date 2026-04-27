<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Dto\Attribute;

use DualMedia\DtoRequestBundle\Dto\Model\Dynamic;
use DualMedia\DtoRequestBundle\Dto\Model\Literal;
use DualMedia\DtoRequestBundle\Metadata\Enum\BagEnum;
use DualMedia\DtoRequestBundle\Type\TypeUtils;
use Symfony\Component\TypeInfo\Type;
use Symfony\Component\TypeInfo\Type\BuiltinType;
use Symfony\Component\TypeInfo\TypeIdentifier;
use Symfony\Component\Validator\Constraint;

/**
 * Field declaration, used for FindX attributes.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::IS_REPEATABLE)]
readonly class Field
{
    /**
     * @param Type|\Closure(): Type $type
     * @param list<Constraint>|Constraint $constraints
     * @param list<object> $meta attribute instances forwarded to the virtual
     *                          property's metadata (e.g. `new FromKey()`,
     *                          `new Format('Y-m-d')`, `new WithAllowedEnum([...])`)
     * @see TypeUtils for repeatable type closures
     */
    public function __construct(
        public string $target,
        public string|Dynamic|Literal $input,
        public Type|\Closure $type = new BuiltinType(TypeIdentifier::STRING),
        public array|Constraint $constraints = [],
        public BagEnum|null $bag = null,
        public string|null $description = null,
        public array $meta = []
    ) {
    }
}
