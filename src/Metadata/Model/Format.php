<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Metadata\Model;

readonly class Format
{
    /**
     * @param non-empty-string $format
     */
    public function __construct(
        public string $format
    ) {
    }
}
