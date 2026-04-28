<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Feature\Resolver;

use DualMedia\DtoRequestBundle\Resolve\DtoResolver;
use DualMedia\DtoRequestBundle\Tests\Fixture\Dto\ExtraGroupDto;
use DualMedia\DtoRequestBundle\Tests\Fixture\Dto\ExtraGroupValidatedDto;
use DualMedia\DtoRequestBundle\Tests\Fixture\Service\TestExtraGroupProvider;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\KernelTestCase;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;

#[Group('feature')]
#[Group('resolver')]
class ValidateWithGroupsDefaultTest extends KernelTestCase
{
    private DtoResolver $resolver;

    protected function setUp(): void
    {
        $this->resolver = static::getService(DtoResolver::class);
    }

    public function testBaselineDtoZeroPasses(): void
    {
        $dto = $this->resolver->resolve(
            ExtraGroupDto::class,
            new Request(request: ['value' => '0'])
        );

        // 'extra' group is not invoked → Positive doesn't fire → only NotNull
        // is in play, value=0 satisfies it.
        static::assertTrue($dto->isValid(), (string)$dto->getConstraintViolationList());
    }

    public function testBaselineDtoMissingTriggersNotNull(): void
    {
        $dto = $this->resolver->resolve(
            ExtraGroupDto::class,
            new Request()
        );

        static::assertFalse($dto->isValid(), 'NotNull must fire when value is missing');
    }

    public function testValidateWithGroupsDtoZeroFailsPositiveWhenExtraGroupActive(): void
    {
        $provider = static::getService(TestExtraGroupProvider::class);
        $provider->groups = ['extra'];

        $dto = $this->resolver->resolve(
            ExtraGroupValidatedDto::class,
            new Request(request: ['value' => '0'])
        );

        static::assertFalse($dto->isValid(), 'Positive(extra) must fire on 0 when the extra group is requested');
    }

    public function testValidateWithGroupsDtoMissingFailsNotNullEvenWhenOnlyExtraReturned(): void
    {
        $provider = static::getService(TestExtraGroupProvider::class);
        $provider->groups = ['extra'];

        $dto = $this->resolver->resolve(
            ExtraGroupValidatedDto::class,
            new Request()
        );

        // Even when the closure returns only ['extra'], Default must remain in
        // play so NotNull (Default group) still surfaces.
        static::assertFalse($dto->isValid(), 'NotNull (Default group) must still fire alongside the extra group');
    }
}
