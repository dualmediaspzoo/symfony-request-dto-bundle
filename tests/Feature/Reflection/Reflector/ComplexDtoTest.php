<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Feature\Reflection\Reflector;

use DualMedia\DtoRequestBundle\Coercer\IntegerCoercer;
use DualMedia\DtoRequestBundle\Metadata\Model\Property;
use DualMedia\DtoRequestBundle\Tests\Feature\Reflection\AbstractReflectorTestCase;
use DualMedia\DtoRequestBundle\Tests\Fixture\Dto\ComplexDto;
use PHPUnit\Framework\Attributes\Group;

#[Group('feature')]
#[Group('reflection')]
#[Group('reflector')]
class ComplexDtoTest extends AbstractReflectorTestCase
{
    public function test(): void
    {
        $reflection = $this->service->reflect(ComplexDto::class);

        static::assertArrayHasKey('someInput', $reflection->fields);
        static::assertInstanceOf(Property::class, $someInput = $reflection->fields['someInput']);

        static::assertEquals('someInput', $someInput->name);
        static::assertEquals(IntegerCoercer::class, $someInput->coercer);
        static::assertEquals('some-path', $someInput->path);
        static::assertEmpty($someInput->constraints);
        static::assertEmpty($someInput->virtual);

        print_r($reflection);
    }
}
