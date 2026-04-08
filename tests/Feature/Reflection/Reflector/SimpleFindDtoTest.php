<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Feature\Reflection\Reflector;

use DualMedia\DtoRequestBundle\Metadata\Model\Property;
use DualMedia\DtoRequestBundle\Tests\Feature\Reflection\AbstractReflectorTestCase;
use DualMedia\DtoRequestBundle\Tests\Fixture\Dto\SimpleFindDto;
use DualMedia\DtoRequestBundle\Tests\Fixture\Entity\SimpleEntity;
use DualMedia\DtoRequestBundle\Type\TypeInfoUtils;
use PHPUnit\Framework\Attributes\Group;

#[Group('feature')]
#[Group('reflection')]
#[Group('reflector')]
class SimpleFindDtoTest extends AbstractReflectorTestCase
{
    public function test(): void
    {
        $reflection = $this->service->reflect(SimpleFindDto::class);

        static::assertArrayHasKey('entity', $reflection->fields);
        static::assertInstanceOf(Property::class, $property = $reflection->fields['entity']);
        static::assertNull($property->bag);
        static::assertEquals('entity', $property->name);
        static::assertEquals(SimpleEntity::class, TypeInfoUtils::getClassName($property->type));
        static::assertFalse(TypeInfoUtils::isCollection($property->type));

        static::assertArrayHasKey('id', $property->virtual);
        static::assertInstanceOf(Property::class, $virtual = $property->virtual['id']);
        static::assertEquals('id', $virtual->name);
        static::assertEquals('inputId', $virtual->path);
    }
}
