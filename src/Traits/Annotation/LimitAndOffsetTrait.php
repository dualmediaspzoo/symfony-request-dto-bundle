<?php

namespace DualMedia\DtoRequestBundle\Traits\Annotation;

trait LimitAndOffsetTrait
{
    /**
     * Result limit.
     */
    public int|null $limit = null;

    /**
     * Result offset.
     */
    public int|null $offset = null;
}
