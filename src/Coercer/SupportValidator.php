<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Coercer;

use DualMedia\DtoRequestBundle\Coercer\Attribute\Supports;
use DualMedia\DtoRequestBundle\Resolve\TypeInfoHelper;
use Symfony\Component\TypeInfo\Type;

class SupportValidator
{
    /**
     * @var array<string, \Closure(Type): bool>
     */
    private array $cache = [];

    public function __construct(
        private readonly Registry $registry
    ) {
    }

    public function supports(
        Type $type
    ): string|null {
        if (empty($this->cache)) {
            foreach ($this->registry->iterator() as $id => $coercer) {
                $attribute = (new \ReflectionClass($coercer)->getAttributes(Supports::class)[0] ?? null)?->newInstance();
                assert(null !== $attribute);
                /** @var Supports $attribute */
                $this->cache[$id] = $attribute->target;
            }
        }

        $checkType = TypeInfoHelper::getCollectionValueType($type) ?? TypeInfoHelper::unwrap($type);

        return array_find_key(
            $this->cache,
            fn ($closure) => call_user_func($closure, $checkType)
        );
    }
}
