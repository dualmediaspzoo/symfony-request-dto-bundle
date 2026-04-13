<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Feature\Resolver;

use DualMedia\DtoRequestBundle\Resolve\DtoResolver;
use DualMedia\DtoRequestBundle\Tests\Fixture\Dto\MappedToPathDto;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\KernelTestCase;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;

#[Group('feature')]
#[Group('resolver')]
class MappedToPathDtoTest extends KernelTestCase
{
    private DtoResolver $resolver;

    protected function setUp(): void
    {
        $this->resolver = static::getService(DtoResolver::class);
    }

    public function testCallbackViolationIsRoutedToConfiguredPath(): void
    {
        $dto = $this->resolver->resolve(
            MappedToPathDto::class,
            new Request(request: [
                'startsAt' => 10,
                'endsAt' => 5,
            ])
        );

        static::assertFalse($dto->isValid());

        $violations = static::getConstraintViolationsMappedToPropertyPaths($dto->getConstraintViolationList());
        static::assertArrayHasKey('endsAt', $violations);
        static::assertArrayNotHasKey('', $violations);
    }

    public function testValidRangePassesWithoutViolations(): void
    {
        $dto = $this->resolver->resolve(
            MappedToPathDto::class,
            new Request(request: [
                'startsAt' => 5,
                'endsAt' => 10,
            ])
        );

        static::assertTrue($dto->isValid());
    }
}
