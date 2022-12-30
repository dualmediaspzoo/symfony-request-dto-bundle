<?php

namespace DualMedia\DtoRequestBundle\Traits\Annotation;

trait LimitAndOffsetTrait
{
    /**
     * Result limit
     *
     * @var int|null
     */
    public int|null $limit = null;

    /**
     * Result offset
     *
     * @var int|null
     */
    public int|null $offset = null;
}
