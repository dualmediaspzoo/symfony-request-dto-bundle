<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Profiler;

use DualMedia\DtoRequestBundle\Metadata\Model\MainDto;
use DualMedia\DtoRequestBundle\Profiler\DataCollector\DtoDataCollector;
use DualMedia\DtoRequestBundle\Reflection\Interface\MainDtoMemoizerInterface;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Contracts\Service\ResetInterface;

class ProfilingMainDtoMemoizer implements MainDtoMemoizerInterface, ResetInterface
{
    private const string CATEGORY = 'dm_dto';

    /**
     * @var array<string, true>
     */
    private array $seen = [];

    public function __construct(
        private readonly MainDtoMemoizerInterface $inner,
        private readonly Stopwatch $stopwatch,
        private readonly DtoDataCollector $collector
    ) {
    }

    #[\Override]
    public function get(
        string $class
    ): MainDto|null {
        $hit = isset($this->seen[$class]);

        if ($hit) {
            $this->collector->recordMemoizerLookup($class, true, 0.0);

            return $this->inner->get($class);
        }

        $event = $this->stopwatch->start('dto.memoizer.miss', self::CATEGORY);
        $result = $this->inner->get($class);
        $ms = (float)$event->stop()->getDuration();

        $this->seen[$class] = true;
        $this->collector->recordMemoizerLookup($class, false, $ms);

        return $result;
    }

    #[\Override]
    public function reset(): void
    {
        $this->seen = [];

        if ($this->inner instanceof ResetInterface) {
            $this->inner->reset();
        }
    }
}
