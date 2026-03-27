<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Dto\Attribute;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use Symfony\Component\Validator\Constraints\GroupSequence;
use Symfony\Component\Validator\GroupProviderInterface;

/**
 * Place on dto to provide groups which will then be passed to the validator on this object.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class ValidateWithGroups
{
    /**
     * @param \Closure(GroupProviderInterface, AbstractDto): (string[]|string[][]|GroupSequence) $closure
     */
    public function __construct(
        public \Closure $closure
    ) {
    }
}
