<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Unit\Reflection;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Dto\Attribute\Field;
use DualMedia\DtoRequestBundle\Dto\Attribute\FindOneBy;
use DualMedia\DtoRequestBundle\Dto\Attribute\WithObjectProvider;
use DualMedia\DtoRequestBundle\Reflection\Factory\PropertyFactory;
use DualMedia\DtoRequestBundle\Reflection\MetaReflector;
use DualMedia\DtoRequestBundle\Reflection\Reflector;
use DualMedia\DtoRequestBundle\Reflection\VirtualReflector;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\TypeInfo\TypeResolver\TypeResolver;

#[Group('unit')]
#[Group('reflection')]
class WithObjectProviderValidationTest extends TestCase
{
    public function testThrowsWhenServiceNotInLocator(): void
    {
        $reflector = $this->buildReflector(new ServiceLocator([]));

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('#[WithObjectProvider]');
        $this->expectExceptionMessage('UnregisteredService');
        $reflector->reflect(WithObjectProviderUnregisteredFixture::class);
    }

    public function testReflectsServiceIdWhenPresent(): void
    {
        $service = new UnregisteredService();
        $locator = new ServiceLocator([UnregisteredService::class => static fn () => $service]);
        $reflector = $this->buildReflector($locator);

        $main = $reflector->reflect(WithObjectProviderUnregisteredFixture::class);
        static::assertSame(UnregisteredService::class, $main->fields['thing']->objectProviderServiceId);
    }

    private function buildReflector(
        ServiceLocator $objectProviderLocator
    ): Reflector {
        $supportValidator = new \DualMedia\DtoRequestBundle\Coercer\SupportValidator(
            new \DualMedia\DtoRequestBundle\Coercer\Registry(new ServiceLocator([]))
        );

        return new Reflector(
            virtualReflector: new VirtualReflector(new PropertyFactory($supportValidator)),
            propertyFactory: new PropertyFactory($supportValidator),
            metaReflector: new MetaReflector(),
            typeResolver: TypeResolver::create(),
            groupProviderLocator: new ServiceLocator([]),
            objectProviderLocator: $objectProviderLocator
        );
    }
}

class UnregisteredService
{
}

class WithObjectProviderUnregisteredFixture extends AbstractDto
{
    #[FindOneBy]
    #[Field('id', 'inputId')]
    #[WithObjectProvider(static function (UnregisteredService $svc, array $criteria, array $meta) { return null; })]
    public \stdClass|null $thing = null;
}
