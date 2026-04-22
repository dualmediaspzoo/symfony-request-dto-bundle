<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Metadata\Model;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Provider\Interface\GroupProviderInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @template TGroupProvider of GroupProviderInterface
 * @template TDto of AbstractDto
 *
 * @phpstan-import-type GroupReturnValue from GroupProviderInterface
 *
 * @phpstan-type ValidateWithGroupsClosure \Closure(TGroupProvider, TDto, Request): GroupReturnValue
 */
readonly class ValidateWithGroups
{
    /**
     * @param ValidateWithGroupsClosure $closure
     */
    public function __construct(
        public \Closure $closure
    ) {
    }
}
