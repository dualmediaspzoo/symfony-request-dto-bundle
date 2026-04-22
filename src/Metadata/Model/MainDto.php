<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Metadata\Model;

use DualMedia\DtoRequestBundle\Metadata\Enum\BagEnum;
use Symfony\Component\Validator\Constraint;

/**
 * Main dto object.
 */
readonly class MainDto
{
    /**
     * @param array<string, Property|Dto> $fields
     * @param list<Constraint> $constraints
     * @param list<object> $meta
     */
    public function __construct(
        public array $fields,
        public array $constraints = [],
        public array $meta = [],
        public string|null $validationGroupsServiceId = null,
        public bool $requiresRuntimeResolve = false,
        public bool $childRequiresRuntimeResolve = false,
        public BagEnum|null $defaultBag = null
    ) {
    }
}
