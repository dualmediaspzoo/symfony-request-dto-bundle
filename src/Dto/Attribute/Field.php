<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Dto\Attribute;

use DualMedia\DtoRequestBundle\Dto\Model\Dynamic;
use DualMedia\DtoRequestBundle\Dto\Model\Literal;
use DualMedia\DtoRequestBundle\Metadata\Enum\BagEnum;
use Symfony\Component\Validator\Constraint;

/**
 * Field declaration, used for FindX attributes.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::IS_REPEATABLE)]
readonly class Field
{
    /**
     * @param list<Constraint>|Constraint $constraints
     */
    public function __construct(
        public string $target,
        public string|Dynamic|Literal $input,
        public Type|null $type = null,
        public array|Constraint $constraints = [],
        public BagEnum|null $bag = null
    ) {
    }
}
