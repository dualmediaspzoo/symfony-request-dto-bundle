<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Traits\Provider;

use DualMedia\DtoRequestBundle\Attribute\Dto\AsDoctrineReference;
use DualMedia\DtoRequestBundle\Interface\Attribute\DtoFindMetaAttributeInterface;

trait MetadataTrait
{
    /**
     * @param list<DtoFindMetaAttributeInterface> $metadata
     */
    protected function metaAsReference(
        array $metadata
    ): bool {
        foreach ($metadata as $attribute) {
            if ($attribute instanceof AsDoctrineReference) {
                return true;
            }
        }

        return false;
    }
}
