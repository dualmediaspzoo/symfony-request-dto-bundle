<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Resolve;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Dto\Event\PropertyMetaEvent;
use DualMedia\DtoRequestBundle\Metadata\Enum\BagEnum;
use DualMedia\DtoRequestBundle\Metadata\Model\MainDto;
use DualMedia\DtoRequestBundle\Resolve\Handler\FieldHandlerInterface;
use DualMedia\DtoRequestBundle\Resolve\Model\PendingEntityValue;
use DualMedia\DtoRequestBundle\Resolve\Model\PendingValue;
use DualMedia\DtoRequestBundle\Util;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Extractor
{
    /**
     * @param iterable<FieldHandlerInterface> $handlers
     */
    public function __construct(
        private readonly iterable $handlers,
        private readonly EventDispatcherInterface $dispatcher
    ) {
    }

    /**
     * Recursively walks the metadata tree, extracting and coercing values
     * into PendingValue entries without validating.
     *
     * @param list<string> $prefix path segments from parent DTOs
     * @param list<PendingValue|PendingEntityValue> $pending collected entries (mutated)
     * @param array<string, true> $seen normalized paths already dispatched (mutated)
     */
    public function extract(
        MainDto $metadata,
        AbstractDto $dto,
        BagAccessor $accessor,
        BagEnum $defaultBag,
        array $prefix = [],
        array &$pending = [],
        array &$seen = []
    ): bool {
        $anyVisited = false;

        foreach ($metadata->fields as $name => $meta) {
            foreach ($this->handlers as $handler) {
                if (!$handler->supports($meta)) {
                    continue;
                }

                $visited = $handler->handle($dto, $name, $meta, $accessor, $defaultBag, $prefix, $pending, $seen);

                if ($visited) {
                    $dto->visit($name);
                    $anyVisited = true;
                }

                $normalizedPath = Util::buildNonUniquePropertyPath([...$prefix, $meta->getRealPath()]);

                if (!isset($seen[$normalizedPath])) {
                    $seen[$normalizedPath] = true;
                    $this->dispatcher->dispatch(new PropertyMetaEvent($dto, $normalizedPath, $meta));
                }

                break;
            }
        }

        return $anyVisited;
    }
}
