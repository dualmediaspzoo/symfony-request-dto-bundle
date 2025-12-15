<?php

namespace DualMedia\DtoRequestBundle\Attribute\Dto;

use DualMedia\DtoRequestBundle\Interface\Attribute\DtoAttributeInterface;

/**
 * A date(-time) format annotation.
 *
 * Used to create a {@link \DateTimeImmutable} object to be passed into the dto.
 *
 * @see https://www.php.net/manual/en/datetime.createfromformat.php Allowed date formats
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class Format implements DtoAttributeInterface
{
    public function __construct(
        public readonly string|null $format = null
    ) {
    }
}
