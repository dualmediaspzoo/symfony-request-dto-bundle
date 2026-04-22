<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Dto\Attribute;

use DualMedia\DtoRequestBundle\Metadata\Model\ValidateWithGroups as ValidateWithGroupsModel;

/**
 * Place on dto to provide groups which will then be passed to the validator on this object.
 *
 * @phpstan-import-type ValidateWithGroupsClosure from ValidateWithGroupsModel
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class ValidateWithGroups
{
    /**
     * @param ValidateWithGroupsClosure $closure
     */
    public function __construct(
        public \Closure $closure
    ) {
    }
}
