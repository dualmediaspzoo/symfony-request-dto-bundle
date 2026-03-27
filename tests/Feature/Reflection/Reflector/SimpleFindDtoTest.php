<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Feature\Reflection\Reflector;

use DualMedia\DtoRequestBundle\Metadata\Model\Property;
use DualMedia\DtoRequestBundle\Tests\Feature\Reflection\AbstractReflectorTestCase;
use DualMedia\DtoRequestBundle\Tests\Fixture\Dto\SimpleFindDto;
use DualMedia\DtoRequestBundle\Tests\Fixture\Entity\SimpleEntity;
use PHPUnit\Framework\Attributes\Group;

#[Group('feature')]
#[Group('reflection')]
#[Group('reflector')]
class SimpleFindDtoTest extends AbstractReflectorTestCase
{
    public function test(): void
    {
        $reflection = $this->service->reflect(SimpleFindDto::class);

        static::assertArrayHasKey('entity', $reflection);
        static::assertInstanceOf(Property::class, $property = $reflection['entity']);
        static::assertNull($property->bag);
        static::assertEquals('entity', $property->name);
        static::assertEquals('object', $property->type->type);
        static::assertNull($property->type->collection);
        static::assertEquals(SimpleEntity::class, $property->type->fqcn);

        static::assertArrayHasKey('id', $property->virtual);
        static::assertInstanceOf(Property::class, $virtual = $property->virtual['id']);
        static::assertEquals('id', $virtual->name);
        static::assertEquals('inputId', $virtual->input);
    }
}
