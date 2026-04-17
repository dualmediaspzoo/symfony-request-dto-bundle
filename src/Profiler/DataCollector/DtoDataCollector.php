<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Profiler\DataCollector;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

class DtoDataCollector extends DataCollector
{
    public function __construct()
    {
        $this->reset();
    }

    /**
     * @param array{
     *     class: string,
     *     total_ms: float,
     *     phase1_ms: float,
     *     phase2_ms: float,
     *     phase3_ms: float,
     *     phase4_ms: float,
     *     violations: int,
     *     phase2_failures: list<array{path: string, phase: int, messages: list<string>}>,
     *     phase4_violations: list<array{path: string, message: string, constraint: string|null}>
     * } $row
     */
    public function addResolverRow(
        array $row
    ): void {
        $this->data['resolver'][] = $row;
    }

    /**
     * @param array{
     *     class: string,
     *     depth: int,
     *     fields_walked: int,
     *     events_dispatched: int,
     *     ms: float
     * } $row
     */
    public function addExtractorRow(
        array $row
    ): void {
        $this->data['extractor'][] = $row;
    }

    /**
     * @param array{
     *     method: string,
     *     class: string|null,
     *     fields_touched: int,
     *     runtime_resolved_fields: int,
     *     ms: float
     * } $row
     */
    public function addRuntimeHelperRow(
        array $row
    ): void {
        $this->data['runtime_helper'][] = $row;
    }

    public function recordMemoizerLookup(
        string $class,
        bool $hit,
        float $ms
    ): void {
        if (!isset($this->data['memoizer'][$class])) {
            $this->data['memoizer'][$class] = [
                'class' => $class,
                'lookups' => 0,
                'hits' => 0,
                'misses' => 0,
                'first_miss_ms' => 0.0,
            ];
        }

        ++$this->data['memoizer'][$class]['lookups'];

        if ($hit) {
            ++$this->data['memoizer'][$class]['hits'];
        } else {
            ++$this->data['memoizer'][$class]['misses'];

            if (0.0 === $this->data['memoizer'][$class]['first_miss_ms']) {
                $this->data['memoizer'][$class]['first_miss_ms'] = $ms;
            }
        }
    }

    #[\Override]
    public function collect(
        Request $request,
        Response $response,
        \Throwable|null $exception = null
    ): void {
        // rows are pushed live during the request; nothing to gather here
    }

    #[\Override]
    public function reset(): void
    {
        $this->data = [
            'resolver' => [],
            'extractor' => [],
            'memoizer' => [],
            'runtime_helper' => [],
        ];
    }

    /**
     * @return list<array{class: string, total_ms: float, phase1_ms: float, phase2_ms: float, phase3_ms: float, phase4_ms: float, violations: int, phase2_failures: list<array{path: string, phase: int, messages: list<string>}>, phase4_violations: list<array{path: string, message: string, constraint: string|null}>}>
     */
    public function getResolverRows(): array
    {
        return $this->data['resolver'];
    }

    /**
     * @return list<array{class: string, depth: int, fields_walked: int, events_dispatched: int, ms: float}>
     */
    public function getExtractorRows(): array
    {
        return $this->data['extractor'];
    }

    /**
     * @return array<string, array{class: string, lookups: int, hits: int, misses: int, first_miss_ms: float}>
     */
    public function getMemoizerRows(): array
    {
        return $this->data['memoizer'];
    }

    /**
     * @return list<array{method: string, class: string|null, fields_touched: int, runtime_resolved_fields: int, ms: float}>
     */
    public function getRuntimeHelperRows(): array
    {
        return $this->data['runtime_helper'];
    }

    public function getTotalMs(): float
    {
        $total = 0.0;

        foreach ($this->data['resolver'] as $row) {
            $total += $row['total_ms'];
        }

        return $total;
    }

    public function getHitRatio(): float
    {
        $hits = 0;
        $lookups = 0;

        foreach ($this->data['memoizer'] as $row) {
            $hits += $row['hits'];
            $lookups += $row['lookups'];
        }

        return $lookups > 0 ? $hits / $lookups : 0.0;
    }

    #[\Override]
    public function getName(): string
    {
        return 'dm_dto';
    }
}
