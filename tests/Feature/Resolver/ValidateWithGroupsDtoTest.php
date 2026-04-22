<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Feature\Resolver;

use DualMedia\DtoRequestBundle\Resolve\DtoResolver;
use DualMedia\DtoRequestBundle\Tests\Fixture\Dto\ValidateWithGroupsDto;
use DualMedia\DtoRequestBundle\Tests\Fixture\Service\TestGroupProvider;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\KernelTestCase;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;

#[Group('feature')]
#[Group('resolver')]
class ValidateWithGroupsDtoTest extends KernelTestCase
{
    private DtoResolver $resolver;

    private TestGroupProvider $provider;

    protected function setUp(): void
    {
        $this->resolver = static::getService(DtoResolver::class);
        $this->provider = static::getService(TestGroupProvider::class);
        $this->provider->groups = ['Default'];
    }

    public function testStrictGroupFiresNotBlankViolation(): void
    {
        $this->provider->groups = ['strict'];

        $dto = $this->resolver->resolve(
            ValidateWithGroupsDto::class,
            new Request()
        );

        static::assertFalse($dto->isValid());

        $violations = static::getConstraintViolationsMappedToPropertyPaths($dto->getConstraintViolationList());
        static::assertArrayHasKey('name', $violations);
    }

    public function testDefaultGroupDoesNotFireGroupedConstraint(): void
    {
        $this->provider->groups = ['Default'];

        $dto = $this->resolver->resolve(
            ValidateWithGroupsDto::class,
            new Request()
        );

        static::assertTrue($dto->isValid());
    }

    public function testStrictGroupPassesWhenNameProvided(): void
    {
        $this->provider->groups = ['strict'];

        $dto = $this->resolver->resolve(
            ValidateWithGroupsDto::class,
            new Request(request: ['name' => 'hello'])
        );

        static::assertTrue($dto->isValid());
        static::assertSame('hello', $dto->name);
    }
}
