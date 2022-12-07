<?php

namespace DM\DtoRequestBundle\Annotations\Dto;

use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use DM\DtoRequestBundle\Interfaces\Attribute\DtoAnnotationInterface;

/**
 * A date(-time) format annotation
 *
 * Used to create a {@link \DateTimeImmutable} object to be passed into the dto.
 *
 * @see https://www.php.net/manual/en/datetime.createfromformat.php Allowed date formats
 *
 * @Annotation
 * @NamedArgumentConstructor()
 */
class Format implements DtoAnnotationInterface
{
    public ?string $format;

    public function __construct(
        ?string $format = null
    ) {
        $this->format = $format;
    }
}
