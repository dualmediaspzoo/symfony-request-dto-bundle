<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\OpenApi\Model;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;

readonly class DescribedDto
{
    /**
     * @param class-string<AbstractDto> $class
     * @param list<DescribedField> $fields
     * @param list<object> $meta metadata objects carried through from MainDto::$meta
     */
    public function __construct(
        public string $class,
        public array $fields,
        public array $meta = []
    ) {
    }
}
