<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Coercer\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS)]
class Supports
{
    public function __construct(
        public readonly \Closure $target
    ) {
    }
}
