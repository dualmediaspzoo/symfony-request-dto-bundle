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

    #[\Override]
    public function getLimit(): int|null
    {
        return $this->limit;
    }

    #[\Override]
    public function getOffset(): int|null
    {
        return $this->offset;
    }
}
