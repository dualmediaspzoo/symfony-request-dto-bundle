<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Feature\Profiler;

use DualMedia\DtoRequestBundle\Profiler\DataCollector\DtoDataCollector;
use DualMedia\DtoRequestBundle\Resolve\Interface\DtoResolverInterface;
use DualMedia\DtoRequestBundle\Tests\Fixture\Dto\ComplexDto;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\KernelTestCase;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;

#[Group('feature')]
#[Group('profiler')]
class DtoDataCollectorTest extends KernelTestCase
{
    private DtoResolverInterface $resolver;

    private DtoDataCollector $collector;

    protected function setUp(): void
    {
        $this->resolver = static::getService(DtoResolverInterface::class);
        $this->collector = static::getService(DtoDataCollector::class);
        $this->collector->reset();
    }

    public function testCollectsResolverRowOnSuccessfulResolution(): void
    {
        $this->resolver->resolve(
            ComplexDto::class,
            new Request(request: [
                'some-path' => '42',
                'verySimpleDto' => [
                    'intField' => '20',
                    'stringField' => 'hello',
                    'dateTime' => '2024-01-15',
                ],
                'listOfDto' => [],
            ])
        );

        $rows = $this->collector->getResolverRows();
        static::assertCount(1, $rows);

        $row = $rows[0];
        static::assertSame(ComplexDto::class, $row['class']);
        static::assertSame(0, $row['violations']);
        static::assertSame([], $row['phase2_failures']);
        static::assertSame([], $row['phase4_violations']);
        static::assertGreaterThanOrEqual(0.0, $row['phase1_ms']);
        static::assertGreaterThanOrEqual(0.0, $row['phase2_ms']);
        static::assertGreaterThanOrEqual(0.0, $row['phase3_ms']);
        static::assertGreaterThanOrEqual(0.0, $row['phase4_ms']);
        static::assertGreaterThanOrEqual(
            $row['phase1_ms'] + $row['phase2_ms'] + $row['phase3_ms'] + $row['phase4_ms'],
            $row['total_ms']
        );
    }

    public function testCapturesPhase2FailuresWithPropertyPaths(): void
    {
        $this->resolver->resolve(
            ComplexDto::class,
            new Request(request: [
                'verySimpleDto' => [
                    'intField' => 'not-a-number',
                ],
            ])
        );

        $rows = $this->collector->getResolverRows();
        static::assertCount(1, $rows);

        $row = $rows[0];
        static::assertGreaterThan(0, $row['violations']);
        static::assertNotEmpty($row['phase2_failures']);

        $paths = array_column($row['phase2_failures'], 'path');
        static::assertContains('verySimpleDto.intField', $paths);

        foreach ($row['phase2_failures'] as $failure) {
            static::assertIsInt($failure['phase']);
            static::assertNotEmpty($failure['messages']);
        }
    }

    public function testExtractorRowsRecordedForNestedDtos(): void
    {
        $this->resolver->resolve(
            ComplexDto::class,
            new Request(request: [
                'some-path' => '1',
                'verySimpleDto' => [
                    'intField' => '20',
                    'stringField' => 'ok',
                ],
            ])
        );

        $rows = $this->collector->getExtractorRows();
        static::assertNotEmpty($rows);

        $classes = array_column($rows, 'class');
        static::assertContains(ComplexDto::class, $classes);

        // the root-level extract() call for ComplexDto should have depth 0
        $root = null;

        foreach ($rows as $row) {
            if (ComplexDto::class === $row['class'] && 0 === $row['depth']) {
                $root = $row;

                break;
            }
        }

        static::assertNotNull($root);
        static::assertGreaterThan(0, $root['fields_walked']);
    }

    public function testMemoizerHitMissTrackingAcrossCalls(): void
    {
        $request = new Request(request: ['some-path' => '1']);

        $this->resolver->resolve(ComplexDto::class, $request);
        $this->resolver->resolve(ComplexDto::class, $request);

        $rows = $this->collector->getMemoizerRows();
        static::assertArrayHasKey(ComplexDto::class, $rows);

        $row = $rows[ComplexDto::class];
        static::assertSame(2, $row['lookups']);
        static::assertSame(1, $row['misses']);
        static::assertSame(1, $row['hits']);
        // first-miss time should be recorded (and non-zero on a real load)
        static::assertGreaterThanOrEqual(0.0, $row['first_miss_ms']);
    }

    public function testResetClearsAllRows(): void
    {
        $this->resolver->resolve(ComplexDto::class, new Request(request: ['some-path' => '1']));

        static::assertNotEmpty($this->collector->getResolverRows());

        $this->collector->reset();

        static::assertSame([], $this->collector->getResolverRows());
        static::assertSame([], $this->collector->getExtractorRows());
        static::assertSame([], $this->collector->getMemoizerRows());
        static::assertSame([], $this->collector->getRuntimeHelperRows());
    }
}
