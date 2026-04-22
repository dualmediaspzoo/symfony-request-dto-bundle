<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Metadata\Model;

readonly class WithErrorPath
{
    /**
     * @param non-empty-string $path
     */
    public function __construct(
        public string $path
    ) {
    }
}
