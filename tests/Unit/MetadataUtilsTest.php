<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Unit;

use DualMedia\DtoRequestBundle\Metadata\Model\FindBy;
use DualMedia\DtoRequestBundle\Metadata\Model\Format;
use DualMedia\DtoRequestBundle\Metadata\Model\Limit;
use DualMedia\DtoRequestBundle\Metadata\Model\OrderBy;
use DualMedia\DtoRequestBundle\MetadataUtils;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[CoversClass(MetadataUtils::class)]
#[Group('unit')]
class MetadataUtilsTest extends TestCase
{
    public function testSingleFindsInstance(): void
    {
        $findBy = new FindBy(true);
        $meta = [$findBy, new Limit(10)];

        static::assertSame($findBy, MetadataUtils::single(FindBy::class, $meta));
    }

    public function testSingleReturnsNullWhenNotFound(): void
    {
        $meta = [new Limit(10)];
        static::assertNull(MetadataUtils::single(FindBy::class, $meta));
    }

    public function testSingleReturnsFirstMatch(): void
    {
        $first = new OrderBy('a', 'ASC');
        $second = new OrderBy('b', 'DESC');
        $meta = [$first, $second];

        static::assertSame($first, MetadataUtils::single(OrderBy::class, $meta));
    }

    public function testExistsTrue(): void
    {
        $meta = [new FindBy(false)];
        static::assertTrue(MetadataUtils::exists(FindBy::class, $meta));
    }

    public function testExistsFalse(): void
    {
        $meta = [new Limit(10)];
        static::assertFalse(MetadataUtils::exists(FindBy::class, $meta));
    }

    public function testExistsEmptyArray(): void
    {
        static::assertFalse(MetadataUtils::exists(FindBy::class, []));
    }

    public function testListFiltersInstances(): void
    {
        $o1 = new OrderBy('a', 'ASC');
        $o2 = new OrderBy('b', 'DESC');
        $meta = [$o1, new Limit(10), $o2, new Format('Y-m-d')];

        $result = MetadataUtils::list(OrderBy::class, $meta);
        static::assertCount(2, $result);
        static::assertSame($o1, $result[0]);
        static::assertSame($o2, $result[1]);
    }

    public function testListReturnsEmptyWhenNoMatch(): void
    {
        $meta = [new Limit(10)];
        static::assertSame([], MetadataUtils::list(FindBy::class, $meta));
    }

    public function testListEmptyArray(): void
    {
        static::assertSame([], MetadataUtils::list(OrderBy::class, []));
    }
}
