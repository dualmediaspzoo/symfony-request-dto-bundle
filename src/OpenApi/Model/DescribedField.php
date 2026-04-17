<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\OpenApi\Model;

use DualMedia\DtoRequestBundle\Metadata\Enum\BagEnum;
use Symfony\Component\Validator\Constraint;

readonly class DescribedField
{
    /**
     * @param list<Constraint> $constraints
     * @param list<DescribedField> $children nested DTO fields; empty for leaves
     * @param list<string|int> $enumCases resolved (post FromKey/label-processor) enum values
     * @param list<object> $meta metadata objects carried through from Property::$meta
     */
    public function __construct(
        public string $name,
        public string $path,
        public BagEnum $bag,
        public string $oaType,
        public bool $isCollection,
        public bool $required,
        public bool $nullable,
        public array $constraints = [],
        public array $children = [],
        public array $enumCases = [],
        public array $meta = []
    ) {
    }
}
