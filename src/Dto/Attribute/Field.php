<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Dto\Attribute;

use Symfony\Component\Validator\Constraint;

/**
 * Field declaration, used for FindX attributes.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::IS_REPEATABLE)]
readonly class Field
{
    /**
     * @param iterable<Constraint>|Constraint $constraints
     */
    public function __construct(
        public string $target,
        public string $input,
        public iterable|Constraint $constraints = []
    ) {
    }
}
