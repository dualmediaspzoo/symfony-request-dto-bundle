<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Unit\Dto\Util;

use DualMedia\DtoRequestBundle\Dto\Enum\Time;
use DualMedia\DtoRequestBundle\Dto\Util\TimeUtils;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[CoversClass(TimeUtils::class)]
#[Group('unit')]
class TimeUtilsTest extends TestCase
{
    public function testEnumPresetConvertsToInterval(): void
    {
        $interval = TimeUtils::toInterval(Time::EndOfDay);
        static::assertInstanceOf(\DateInterval::class, $interval);
        static::assertSame('23:59:59', $interval->format('%H:%I:%S'));
    }

    public function testCustomStringConvertsToInterval(): void
    {
        $interval = TimeUtils::toInterval('06:30:15');
        static::assertInstanceOf(\DateInterval::class, $interval);
        static::assertSame('06:30:15', $interval->format('%H:%I:%S'));
    }

    #[DataProvider('provideInvalidTimeThrowsCases')]
    public function testInvalidTimeThrows(
        string $time
    ): void {
        $this->expectException(\InvalidArgumentException::class);

        TimeUtils::toInterval($time);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideInvalidTimeThrowsCases(): iterable
    {
        yield 'missing seconds' => ['10:30'];
        yield 'not a time' => ['nonsense'];
        yield 'empty' => [''];
    }
}
