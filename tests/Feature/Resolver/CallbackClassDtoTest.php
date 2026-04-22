<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Feature\Resolver;

use DualMedia\DtoRequestBundle\Resolve\DtoResolver;
use DualMedia\DtoRequestBundle\Tests\Fixture\Dto\CallbackClassDto;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\KernelTestCase;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;

#[Group('feature')]
#[Group('resolver')]
class CallbackClassDtoTest extends KernelTestCase
{
    private DtoResolver $resolver;

    protected function setUp(): void
    {
        $this->resolver = static::getService(DtoResolver::class);
    }

    public function testValidValue(): void
    {
        $dto = $this->resolver->resolve(
            CallbackClassDto::class,
            new Request(request: [
                'name' => 'valid',
            ])
        );

        static::assertTrue($dto->isValid());
        static::assertSame('valid', $dto->name);
    }

    public function testClassCallbackTriggersViolation(): void
    {
        $dto = $this->resolver->resolve(
            CallbackClassDto::class,
            new Request(request: [
                'name' => 'invalid',
            ])
        );

        static::assertFalse($dto->isValid());

        $violations = static::getConstraintViolationsMappedToPropertyPaths($dto->getConstraintViolationList());
        static::assertArrayHasKey('name', $violations);
    }
}
