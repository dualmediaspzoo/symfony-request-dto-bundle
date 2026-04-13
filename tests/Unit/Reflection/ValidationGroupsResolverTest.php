<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Unit\Reflection;

use DualMedia\DtoRequestBundle\Reflection\ReflectionUtils;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\ServiceLocator;

#[CoversClass(ReflectionUtils::class)]
#[Group('unit')]
#[Group('reflection')]
class ValidationGroupsResolverTest extends TestCase
{
    public function testResolvesByTypeHint(): void
    {
        $closure = static fn (\stdClass $p, object $dto) => ['g'];

        static::assertSame(\stdClass::class, ReflectionUtils::resolveServiceId($closure));
    }

    public function testAutowireServiceOverridesTypeHint(): void
    {
        $closure = static fn (#[Autowire(service: 'custom.service.id')] \stdClass $p, object $dto) => ['g'];

        static::assertSame('custom.service.id', ReflectionUtils::resolveServiceId($closure));
    }

    public function testAutowireStringShorthand(): void
    {
        // "@foo" shorthand is parsed into a Reference internally
        $closure = static fn (#[Autowire('@some.service')] \stdClass $p, object $dto) => ['g'];

        static::assertSame('some.service', ReflectionUtils::resolveServiceId($closure));
    }

    public function testThrowsOnNoParameters(): void
    {
        $closure = static fn () => ['g'];

        $this->expectException(\LogicException::class);
        ReflectionUtils::resolveServiceId($closure);
    }

    public function testThrowsOnUntypedParameter(): void
    {
        $closure = static fn ($p, object $dto) => ['g'];

        $this->expectException(\LogicException::class);
        ReflectionUtils::resolveServiceId($closure);
    }

    public function testThrowsOnBuiltinParameter(): void
    {
        $closure = static fn (string $p, object $dto) => ['g'];

        $this->expectException(\LogicException::class);
        ReflectionUtils::resolveServiceId($closure);
    }

    public function testValidateWrapsInvalidClosureMessage(): void
    {
        $closure = static fn () => null;
        $locator = new ServiceLocator([]);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Invalid #[X] closure on Some\\Class: ');
        ReflectionUtils::resolveAndValidateServiceId($closure, $locator, '#[X]', 'Some\\Class', 'a tag');
    }

    public function testValidateThrowsWhenServiceMissingFromLocator(): void
    {
        $closure = static fn (\stdClass $p) => null;
        $locator = new ServiceLocator([]);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('#[X] on Some\\Class references service "stdClass" which is not tagged as a tag.');
        ReflectionUtils::resolveAndValidateServiceId($closure, $locator, '#[X]', 'Some\\Class', 'a tag');
    }

    public function testValidateReturnsServiceIdWhenPresent(): void
    {
        $closure = static fn (\stdClass $p) => null;
        $locator = new ServiceLocator([\stdClass::class => static fn () => new \stdClass()]);

        $id = ReflectionUtils::resolveAndValidateServiceId($closure, $locator, '#[X]', 'Some\\Class', 'a tag');
        static::assertSame(\stdClass::class, $id);
    }
}
