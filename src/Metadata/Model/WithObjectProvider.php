<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Metadata\Model;

/**
 * @see \DualMedia\DtoRequestBundle\Dto\Attribute\WithObjectProvider for the type declaration
 */
readonly class WithObjectProvider
{
    public function __construct(
        public \Closure $closure
    ) {
    }
}
