<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Coercer\Attribute;

use Symfony\Component\TypeInfo\Type;

/**
 * Needed on coercers to mark support for types.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class Supports
{
    /**
     * @param \Closure(Type): bool $target
     */
    public function __construct(
        public readonly \Closure $target
    ) {
    }
}
