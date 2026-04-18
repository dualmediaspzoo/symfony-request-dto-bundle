<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Provider\Interface;

use DualMedia\DtoRequestBundle\Dto\Attribute\ValidateWithGroups;
use Symfony\Component\Validator\Constraints\GroupSequence;

/**
 * Marker interface to be used on instances that you want autoregistered with {@link ValidateWithGroups}.
 *
 * @phpstan-type GroupReturnValue string|GroupSequence|list<string|GroupSequence>
 */
interface GroupProviderInterface
{
}
