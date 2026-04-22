<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Unit\Coercer;

use DualMedia\DtoRequestBundle\Coercer\Interface\CoercerInterface;
use DualMedia\DtoRequestBundle\Coercer\Registry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ServiceLocator;

#[CoversClass(Registry::class)]
#[Group('unit')]
#[Group('coercer')]
class RegistryTest extends TestCase
{
    public function testGetReturnsCoercer(): void
    {
        $coercer = $this->createMock(CoercerInterface::class);
        $locator = new ServiceLocator([
            'test_coercer' => static fn () => $coercer,
        ]);

        $registry = new Registry($locator);
        static::assertSame($coercer, $registry->get('test_coercer'));
    }

    public function testIteratorReturnsLocator(): void
    {
        $locator = new ServiceLocator([]);
        $registry = new Registry($locator);

        static::assertSame($locator, $registry->iterator());
    }
}
