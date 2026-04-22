<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Profiler;

use DualMedia\DtoRequestBundle\Metadata\Model\MainDto;
use DualMedia\DtoRequestBundle\Profiler\DataCollector\DtoDataCollector;
use DualMedia\DtoRequestBundle\Reflection\Interface\RuntimeResolveInterface;
use Symfony\Component\Stopwatch\Stopwatch;

class ProfilingRuntimeResolve implements RuntimeResolveInterface
{
    private const string CATEGORY = 'dm_dto';

    public function __construct(
        private readonly RuntimeResolveInterface $inner,
        private readonly Stopwatch $stopwatch,
        private readonly DtoDataCollector $collector
    ) {
    }

    #[\Override]
    public function prepareForCache(
        MainDto $mainDto
    ): MainDto {
        $event = $this->stopwatch->start('dto.runtime_helper.prepareForCache', self::CATEGORY);
        $result = $this->inner->prepareForCache($mainDto);
        $ms = (float)$event->stop()->getDuration();

        $this->collector->addRuntimeHelperRow([
            'method' => 'prepareForCache',
            'class' => null,
            'fields_touched' => count($mainDto->fields),
            'runtime_resolved_fields' => $this->countRuntimeResolved($result),
            'ms' => $ms,
        ]);

        return $result;
    }

    #[\Override]
    public function restoreRuntimeConstraints(
        string $class,
        MainDto $mainDto
    ): MainDto {
        $event = $this->stopwatch->start('dto.runtime_helper.restoreRuntimeConstraints', self::CATEGORY);
        $result = $this->inner->restoreRuntimeConstraints($class, $mainDto);
        $ms = (float)$event->stop()->getDuration();

        $this->collector->addRuntimeHelperRow([
            'method' => 'restoreRuntimeConstraints',
            'class' => $class,
            'fields_touched' => count($mainDto->fields),
            'runtime_resolved_fields' => $this->countRuntimeResolved($mainDto),
            'ms' => $ms,
        ]);

        return $result;
    }

    private function countRuntimeResolved(
        MainDto $mainDto
    ): int {
        $count = $mainDto->requiresRuntimeResolve ? 1 : 0;

        foreach ($mainDto->fields as $field) {
            if ($field->requiresRuntimeResolve) {
                ++$count;
            }
        }

        return $count;
    }
}
