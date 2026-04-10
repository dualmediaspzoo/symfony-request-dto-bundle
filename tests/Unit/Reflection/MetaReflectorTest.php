<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Unit\Reflection;

use Doctrine\Common\Collections\Order;
use DualMedia\DtoRequestBundle\Dto\Attribute\AsDoctrineReference as AsDoctrineReferenceAttribute;
use DualMedia\DtoRequestBundle\Dto\Attribute\FindBy as FindByAttribute;
use DualMedia\DtoRequestBundle\Dto\Attribute\FindOneBy as FindOneByAttribute;
use DualMedia\DtoRequestBundle\Dto\Attribute\Format as FormatAttribute;
use DualMedia\DtoRequestBundle\Dto\Attribute\FromKey as FromKeyAttribute;
use DualMedia\DtoRequestBundle\Dto\Attribute\Limit as LimitAttribute;
use DualMedia\DtoRequestBundle\Dto\Attribute\Offset as OffsetAttribute;
use DualMedia\DtoRequestBundle\Dto\Attribute\OrderBy as OrderByAttribute;
use DualMedia\DtoRequestBundle\Metadata\Model\AsDoctrineReference;
use DualMedia\DtoRequestBundle\Metadata\Model\FindBy;
use DualMedia\DtoRequestBundle\Metadata\Model\Format;
use DualMedia\DtoRequestBundle\Metadata\Model\FromKey;
use DualMedia\DtoRequestBundle\Metadata\Model\Limit;
use DualMedia\DtoRequestBundle\Metadata\Model\Offset;
use DualMedia\DtoRequestBundle\Metadata\Model\OrderBy;
use DualMedia\DtoRequestBundle\Reflection\MetaReflector;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[CoversClass(MetaReflector::class)]
#[Group('unit')]
#[Group('reflection')]
class MetaReflectorTest extends TestCase
{
    private MetaReflector $reflector;

    protected function setUp(): void
    {
        $this->reflector = new MetaReflector();
    }

    public function testFindByAttribute(): void
    {
        $result = $this->reflector->meta([new FindByAttribute()]);
        static::assertCount(1, $result);
        static::assertInstanceOf(FindBy::class, $result[0]);
        static::assertTrue($result[0]->many);
    }

    public function testFindOneByAttribute(): void
    {
        $result = $this->reflector->meta([new FindOneByAttribute()]);
        static::assertCount(1, $result);
        static::assertInstanceOf(FindBy::class, $result[0]);
        static::assertFalse($result[0]->many);
    }

    public function testFormatAttribute(): void
    {
        $result = $this->reflector->meta([new FormatAttribute('Y-m-d')]);
        static::assertCount(1, $result);
        static::assertInstanceOf(Format::class, $result[0]);
        static::assertSame('Y-m-d', $result[0]->format);
    }

    public function testFromKeyAttribute(): void
    {
        $result = $this->reflector->meta([new FromKeyAttribute()]);
        static::assertCount(1, $result);
        static::assertInstanceOf(FromKey::class, $result[0]);
    }

    public function testLimitAttribute(): void
    {
        $result = $this->reflector->meta([new LimitAttribute(25)]);
        static::assertCount(1, $result);
        static::assertInstanceOf(Limit::class, $result[0]);
        static::assertSame(25, $result[0]->count);
    }

    public function testOffsetAttribute(): void
    {
        $result = $this->reflector->meta([new OffsetAttribute(10)]);
        static::assertCount(1, $result);
        static::assertInstanceOf(Offset::class, $result[0]);
        static::assertSame(10, $result[0]->count);
    }

    public function testAsDoctrineReferenceAttribute(): void
    {
        $result = $this->reflector->meta([new AsDoctrineReferenceAttribute()]);
        static::assertCount(1, $result);
        static::assertInstanceOf(AsDoctrineReference::class, $result[0]);
    }

    public function testOrderByAttribute(): void
    {
        $result = $this->reflector->meta([new OrderByAttribute('name', Order::Ascending)]);
        static::assertCount(1, $result);
        static::assertInstanceOf(OrderBy::class, $result[0]);
        static::assertSame('name', $result[0]->field);
        static::assertSame(Order::Ascending->value, $result[0]->order);
    }

    public function testUnknownAttributeIsSkipped(): void
    {
        $result = $this->reflector->meta([new \stdClass()]);
        static::assertSame([], $result);
    }

    public function testEmptyInput(): void
    {
        static::assertSame([], $this->reflector->meta([]));
    }

    public function testMultipleAttributes(): void
    {
        $result = $this->reflector->meta([
            new FindOneByAttribute(),
            new FormatAttribute('Y-m-d'),
            new LimitAttribute(10),
            new \stdClass(),
            new OrderByAttribute('id', Order::Descending),
        ]);

        static::assertCount(4, $result);
        static::assertInstanceOf(FindBy::class, $result[0]);
        static::assertInstanceOf(Format::class, $result[1]);
        static::assertInstanceOf(Limit::class, $result[2]);
        static::assertInstanceOf(OrderBy::class, $result[3]);
    }
}
