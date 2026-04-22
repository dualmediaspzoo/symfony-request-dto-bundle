<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Unit\Type;

use DualMedia\DtoRequestBundle\Reflection\CacheReflector;
use DualMedia\DtoRequestBundle\Type\DtoCacheWarmer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Pkly\ServiceMockHelperTrait;

#[CoversClass(DtoCacheWarmer::class)]
#[Group('unit')]
class DtoCacheWarmerTest extends TestCase
{
    use ServiceMockHelperTrait;

    public function testIsNotOptional(): void
    {
        $warmer = new DtoCacheWarmer([], $this->createMock(CacheReflector::class));
        static::assertFalse($warmer->isOptional());
    }

    public function testWarmUpCallsSaveForEachClass(): void
    {
        $classes = ['App\\Dto\\FooDto', 'App\\Dto\\BarDto'];

        $reflector = $this->createMock(CacheReflector::class);
        $reflector->expects(static::exactly(2))
            ->method('save')
            ->willReturnCallback(function (string $class) use ($classes): bool {
                static::assertContains($class, $classes);

                return true;
            });

        $warmer = new DtoCacheWarmer($classes, $reflector);
        $result = $warmer->warmUp('/tmp/cache');

        static::assertSame($classes, $result);
    }

    public function testWarmUpWithEmptyList(): void
    {
        $reflector = $this->createMock(CacheReflector::class);
        $reflector->expects(static::never())->method('save');

        $warmer = new DtoCacheWarmer([], $reflector);
        static::assertSame([], $warmer->warmUp('/tmp/cache'));
    }
}
