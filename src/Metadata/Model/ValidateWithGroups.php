<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Metadata\Model;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use Symfony\Component\Validator\Constraints\GroupSequence;
use Symfony\Component\Validator\GroupProviderInterface;

/**
 * @phpstan-type ValidateWithGroupsClosure \Closure(GroupProviderInterface, AbstractDto): (string[]|string[][]|GroupSequence)
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