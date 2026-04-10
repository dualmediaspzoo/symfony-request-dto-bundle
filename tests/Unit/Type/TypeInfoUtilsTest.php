<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Unit\Type;

use Doctrine\Common\Collections\ArrayCollection;
use DualMedia\DtoRequestBundle\Type\TypeInfoUtils;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\TypeInfo\Type\BuiltinType;
use Symfony\Component\TypeInfo\Type\CollectionType;
use Symfony\Component\TypeInfo\Type\GenericType;
use Symfony\Component\TypeInfo\Type\ObjectType;
use Symfony\Component\TypeInfo\TypeIdentifier;

#[CoversClass(TypeInfoUtils::class)]
#[Group('unit')]
class TypeInfoUtilsTest extends TestCase
{
    public function testGetClassNameFromObjectType(): void
    {
        $type = new ObjectType(\stdClass::class);
        static::assertSame(\stdClass::class, TypeInfoUtils::getClassName($type));
    }

    public function testGetClassNameFromBuiltinReturnsNull(): void
    {
        $type = new BuiltinType(TypeIdentifier::INT);
        static::assertNull(TypeInfoUtils::getClassName($type));
    }

    public function testIsCollectionTrue(): void
    {
        $type = CollectionType::list(new BuiltinType(TypeIdentifier::INT));
        static::assertTrue(TypeInfoUtils::isCollection($type));
    }

    public function testIsCollectionFalseForScalar(): void
    {
        $type = new BuiltinType(TypeIdentifier::STRING);
        static::assertFalse(TypeInfoUtils::isCollection($type));
    }

    public function testIsCollectionFalseForObject(): void
    {
        $type = new ObjectType(\stdClass::class);
        static::assertFalse(TypeInfoUtils::isCollection($type));
    }

    public function testIsDoctrineCollectionTrue(): void
    {
        $type = new CollectionType(
            new GenericType(
                new ObjectType(ArrayCollection::class),
                new BuiltinType(TypeIdentifier::INT),
                new ObjectType(\stdClass::class)
            )
        );
        static::assertTrue(TypeInfoUtils::isDoctrineCollection($type));
    }

    public function testIsDoctrineCollectionFalseForList(): void
    {
        $type = CollectionType::list(new BuiltinType(TypeIdentifier::INT));
        static::assertFalse(TypeInfoUtils::isDoctrineCollection($type));
    }

    public function testGetCollectionValueType(): void
    {
        $inner = new BuiltinType(TypeIdentifier::INT);
        $type = CollectionType::list($inner);
        $valueType = TypeInfoUtils::getCollectionValueType($type);
        static::assertNotNull($valueType);
    }

    public function testGetCollectionValueTypeNullForNonCollection(): void
    {
        $type = new BuiltinType(TypeIdentifier::STRING);
        static::assertNull(TypeInfoUtils::getCollectionValueType($type));
    }

    public function testGetCollectionValueClassName(): void
    {
        $type = new CollectionType(
            new GenericType(
                new BuiltinType(TypeIdentifier::ARRAY),
                new BuiltinType(TypeIdentifier::INT),
                new ObjectType(\stdClass::class)
            )
        );
        static::assertSame(\stdClass::class, TypeInfoUtils::getCollectionValueClassName($type));
    }

    public function testGetCollectionValueClassNameNullForScalarValues(): void
    {
        $type = CollectionType::list(new BuiltinType(TypeIdentifier::INT));
        static::assertNull(TypeInfoUtils::getCollectionValueClassName($type));
    }

    public function testGetCollectionValueClassNameNullForNonCollection(): void
    {
        $type = new BuiltinType(TypeIdentifier::STRING);
        static::assertNull(TypeInfoUtils::getCollectionValueClassName($type));
    }
}
