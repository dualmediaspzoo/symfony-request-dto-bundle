<?php

namespace DM\DtoRequestBundle\Annotations\Dto;

use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;
use DM\DtoRequestBundle\Interfaces\Attribute\FindInterface;

/**
 * This annotation provides a simple way of declaring type safety for {@link FindInterface} annotations
 *
 * @Annotation
 * @NamedArgumentConstructor()
 */
class Type
{
    public string $type;
    public bool $collection;
    public ?string $subType;

    /**
     * If specified the type will be treated as a date(-time)
     *
     * Applies only to DateTime formats
     *
     * @var Format|null
     */
    public ?Format $format;

    public function __construct(
        string $type = 'string',
        bool $collection = false,
        ?string $subType = null,
        ?Format $format = null
    ) {
        $type = mb_strtolower($type);

        if (null !== $format || in_array($type, ['date', 'datetime'])) { // fake-ish type
            $format ??= new Format();

            $this->type = 'object';
            $this->subType = 'string';
        } else {
            $this->type = in_array($type, ['int', 'string', 'float', 'bool']) ? $type : 'string';
            $this->subType = $subType;
        }

        $this->collection = $collection;
        $this->format = $format;
    }
}
