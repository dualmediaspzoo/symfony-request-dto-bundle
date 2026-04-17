<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Resolve\Interface;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Metadata\Enum\BagEnum;
use DualMedia\DtoRequestBundle\Metadata\Model\MainDto;
use DualMedia\DtoRequestBundle\Resolve\BagAccessor;
use DualMedia\DtoRequestBundle\Resolve\Model\PendingEntityValue;
use DualMedia\DtoRequestBundle\Resolve\Model\PendingValue;

interface ExtractorInterface
{
    /**
     * @param list<string> $prefix
     * @param list<PendingValue|PendingEntityValue> $pending
     * @param array<string, true> $seen
     */
    public function extract(
        MainDto $metadata,
        AbstractDto $dto,
        BagAccessor $accessor,
        BagEnum $defaultBag,
        array $prefix = [],
        array &$pending = [],
        array &$seen = []
    ): bool;
}
