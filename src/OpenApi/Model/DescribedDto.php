<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\OpenApi\Model;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Metadata\Model\Action;

readonly class DescribedDto
{
    /**
     * @param class-string<AbstractDto> $class
     * @param list<DescribedField> $fields
     * @param list<object> $meta metadata objects carried through from MainDto::$meta
     * @param list<Action> $actions all Action metadata found anywhere in the DTO tree
     */
    public function __construct(
        public string $class,
        public array $fields,
        public array $meta = [],
        public array $actions = []
    ) {
    }
}
