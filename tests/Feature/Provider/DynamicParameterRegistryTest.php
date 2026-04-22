<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Feature\Provider;

use DualMedia\DtoRequestBundle\Provider\DynamicParameterRegistry;
use DualMedia\DtoRequestBundle\Tests\Fixture\Enum\PureEnum;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\KernelTestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('feature')]
#[Group('provider')]
class DynamicParameterRegistryTest extends KernelTestCase
{
    public function testGetEnum(): void
    {
        static::assertEquals(
            PureEnum::Alpha,
            self::getService(DynamicParameterRegistry::class)->get('pureAlpha')
        );
    }
}
