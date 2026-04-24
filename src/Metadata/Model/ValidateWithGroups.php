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
 * @phpstan-type ValidateWithGroupsClosureFull \Closure(TGroupProvider, TDto, Request): GroupReturnValue
 * @phpstan-type ValidateWithGroupsClosureProviderDto \Closure(TGroupProvider, TDto): GroupReturnValue
 * @phpstan-type ValidateWithGroupsClosureProviderOnly \Closure(TGroupProvider): GroupReturnValue
 * @phpstan-type ValidateWithGroupsClosure ValidateWithGroupsClosureFull|ValidateWithGroupsClosureProviderDto|ValidateWithGroupsClosureProviderOnly
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
