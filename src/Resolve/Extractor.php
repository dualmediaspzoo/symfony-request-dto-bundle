<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Resolve;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Metadata\Enum\BagEnum;
use DualMedia\DtoRequestBundle\Reflection\CacheReflector;
use DualMedia\DtoRequestBundle\Resolve\Handler\FieldHandlerInterface;
use DualMedia\DtoRequestBundle\Resolve\Model\PendingValue;

class Extractor
{
    /**
     * @param iterable<FieldHandlerInterface> $handlers
     */
    public function __construct(
        private readonly CacheReflector $cacheReflector,
        private readonly iterable $handlers
    ) {
    }

    /**
     * Recursively walks the metadata tree, extracting and coercing values
     * into PendingValue entries without validating.
     *
     * @param list<string> $prefix path segments from parent DTOs
     * @param list<PendingValue> $pending collected entries (mutated)
     */
    public function extract(
        AbstractDto $dto,
        BagAccessor $accessor,
        BagEnum $defaultBag,
        array $prefix = [],
        array &$pending = []
    ): bool {
        if (null === ($metadata = $this->cacheReflector->get($dto::class))) {
            return false;
        }

        $anyVisited = false;

        foreach ($metadata->fields as $name => $meta) {
            foreach ($this->handlers as $handler) {
                if (!$handler->supports($meta)) {
                    continue;
                }

                $visited = $handler->handle($dto, $name, $meta, $accessor, $defaultBag, $prefix, $pending);

                if ($visited) {
                    $dto->visit($name);
                    $anyVisited = true;
                }

                break;
            }
        }

        return $anyVisited;
    }
}
