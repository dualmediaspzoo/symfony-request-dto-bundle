<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Profiler;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Metadata\Enum\BagEnum;
use DualMedia\DtoRequestBundle\Metadata\Model\MainDto;
use DualMedia\DtoRequestBundle\Profiler\DataCollector\DtoDataCollector;
use DualMedia\DtoRequestBundle\Resolve\BagAccessor;
use DualMedia\DtoRequestBundle\Resolve\Interface\ExtractorInterface;
use Symfony\Component\Stopwatch\Stopwatch;

class ProfilingExtractor implements ExtractorInterface
{
    private const string CATEGORY = 'dm_dto';

    public function __construct(
        private readonly ExtractorInterface $inner,
        private readonly Stopwatch $stopwatch,
        private readonly DtoDataCollector $collector
    ) {
    }

    #[\Override]
    public function extract(
        MainDto $metadata,
        AbstractDto $dto,
        BagAccessor $accessor,
        BagEnum $defaultBag,
        array $prefix = [],
        array &$pending = [],
        array &$seen = []
    ): bool {
        $class = $dto::class;
        $short = false !== ($pos = strrpos($class, '\\')) ? substr($class, $pos + 1) : $class;

        $seenBefore = count($seen);
        $depth = count($prefix);
        $fieldsWalked = count($metadata->fields);

        $event = $this->stopwatch->start('dto.extract.'.$short, self::CATEGORY);
        $result = $this->inner->extract($metadata, $dto, $accessor, $defaultBag, $prefix, $pending, $seen);
        $ms = (float)$event->stop()->getDuration();

        $this->collector->addExtractorRow([
            'class' => $class,
            'depth' => $depth,
            'fields_walked' => $fieldsWalked,
            'events_dispatched' => count($seen) - $seenBefore,
            'ms' => $ms,
        ]);

        return $result;
    }
}
