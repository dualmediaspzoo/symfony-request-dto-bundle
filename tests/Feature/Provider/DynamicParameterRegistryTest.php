<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Feature\Provider;

use DualMedia\DtoRequestBundle\Provider\DynamicParameterRegistry;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\KernelTestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('feature')]
#[Group('provider')]
class DynamicParameterRegistryTest extends KernelTestCase
{
    public function testSetup(): void
    {
        print_r(self::getService(DynamicParameterRegistry::class));
    }
}
