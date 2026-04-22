<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Unit\Reflection;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Metadata\Model\Dto;
use DualMedia\DtoRequestBundle\Metadata\Model\MainDto;
use DualMedia\DtoRequestBundle\Metadata\Model\Property;
use DualMedia\DtoRequestBundle\Reflection\Reflector;
use DualMedia\DtoRequestBundle\Reflection\RuntimeResolve;
use DualMedia\DtoRequestBundle\Tests\Fixture\Constraint\UnserializableConstraint;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Pkly\ServiceMockHelperTrait;
use Symfony\Component\TypeInfo\Type;
use Symfony\Component\Validator\Constraints\NotNull;

#[CoversClass(RuntimeResolve::class)]
#[Group('unit')]
#[Group('reflection')]
class RuntimeResolveHelperTest extends TestCase
{
    use ServiceMockHelperTrait;

    private RuntimeResolve $helper;

    protected function setUp(): void
    {
        $this->helper = $this->createRealMockedServiceInstance(RuntimeResolve::class);
    }

    public function testSerializableConstraintsNotFlagged(): void
    {
        $mainDto = new MainDto(
            fields: [
                'name' => new Property('name', Type::string(), constraints: [new NotNull()]),
            ],
            constraints: [new NotNull()]
        );

        $result = $this->helper->prepareForCache($mainDto);

        static::assertFalse($result->requiresRuntimeResolve);
        static::assertFalse($result->childRequiresRuntimeResolve);
        static::assertCount(1, $result->constraints);
        static::assertCount(1, $result->fields['name']->constraints);
    }

    public function testSerializableReturnsSameInstance(): void
    {
        $mainDto = new MainDto(
            fields: [
                'name' => new Property('name', Type::string(), constraints: [new NotNull()]),
            ]
        );

        $result = $this->helper->prepareForCache($mainDto);

        static::assertSame($mainDto, $result);
    }

    public function testUnserializablePropertyConstraintsFlagged(): void
    {
        $unserializable = new UnserializableConstraint(static fn () => true);

        $mainDto = new MainDto(
            fields: [
                'name' => new Property('name', Type::string(), constraints: [$unserializable]),
                'age' => new Property('age', Type::int(), constraints: [new NotNull()]),
            ]
        );

        $result = $this->helper->prepareForCache($mainDto);

        static::assertFalse($result->requiresRuntimeResolve);
        static::assertTrue($result->childRequiresRuntimeResolve);

        static::assertTrue($result->fields['name']->requiresRuntimeResolve);
        static::assertEmpty($result->fields['name']->constraints);

        static::assertFalse($result->fields['age']->requiresRuntimeResolve);
        static::assertCount(1, $result->fields['age']->constraints);
    }

    public function testUnserializableClassConstraintsFlagged(): void
    {
        $unserializable = new UnserializableConstraint(static fn () => true);

        $mainDto = new MainDto(
            fields: [
                'name' => new Property('name', Type::string(), constraints: [new NotNull()]),
            ],
            constraints: [$unserializable]
        );

        $result = $this->helper->prepareForCache($mainDto);

        static::assertTrue($result->requiresRuntimeResolve);
        static::assertFalse($result->childRequiresRuntimeResolve);
        static::assertEmpty($result->constraints);
    }

    public function testUnserializableDtoConstraintsFlagged(): void
    {
        $unserializable = new UnserializableConstraint(static fn () => true);

        $mainDto = new MainDto(
            fields: [
                'child' => new Dto('child', Type::object(AbstractDto::class), constraints: [$unserializable]),
            ]
        );

        $result = $this->helper->prepareForCache($mainDto);

        static::assertFalse($result->requiresRuntimeResolve);
        static::assertTrue($result->childRequiresRuntimeResolve);
        static::assertTrue($result->fields['child']->requiresRuntimeResolve);
        static::assertEmpty($result->fields['child']->constraints);
    }

    public function testUnserializableVirtualPropertyConstraintsFlagged(): void
    {
        $unserializable = new UnserializableConstraint(static fn () => true);

        $mainDto = new MainDto(
            fields: [
                'entity' => new Property(
                    'entity',
                    Type::object(\stdClass::class),
                    constraints: [new NotNull()],
                    virtual: [
                        'id' => new Property('id', Type::int(), constraints: [$unserializable]),
                        'slug' => new Property('slug', Type::string(), constraints: [new NotNull()]),
                    ]
                ),
            ]
        );

        $result = $this->helper->prepareForCache($mainDto);

        static::assertTrue($result->childRequiresRuntimeResolve);

        $entity = $result->fields['entity'];
        assert($entity instanceof Property);

        static::assertFalse($entity->requiresRuntimeResolve);
        static::assertCount(1, $entity->constraints);

        $id = $entity->virtual['id'];
        assert($id instanceof Property);
        static::assertTrue($id->requiresRuntimeResolve);
        static::assertEmpty($id->constraints);

        $slug = $entity->virtual['slug'];
        assert($slug instanceof Property);
        static::assertFalse($slug->requiresRuntimeResolve);
        static::assertCount(1, $slug->constraints);
    }

    public function testRestorePropertyConstraints(): void
    {
        $prepared = new MainDto(
            fields: [
                'name' => new Property('name', Type::string(), requiresRuntimeResolve: true),
            ],
            childRequiresRuntimeResolve: true
        );

        $this->getMockedService(Reflector::class)
            ->expects(static::once())
            ->method('reflectPropertyConstraints')
            ->with($this->dtoClass(), 'name')
            ->willReturn([new NotNull()]);

        $result = $this->helper->restoreRuntimeConstraints($this->dtoClass(), $prepared);

        static::assertFalse($result->requiresRuntimeResolve);
        static::assertFalse($result->childRequiresRuntimeResolve);
        static::assertCount(1, $result->fields['name']->constraints);
        static::assertInstanceOf(NotNull::class, $result->fields['name']->constraints[0]);
        static::assertFalse($result->fields['name']->requiresRuntimeResolve);
    }

    public function testRestoreClassConstraints(): void
    {
        $prepared = new MainDto(
            fields: [],
            requiresRuntimeResolve: true
        );

        $this->getMockedService(Reflector::class)
            ->expects(static::once())
            ->method('reflectClassConstraints')
            ->with($this->dtoClass())
            ->willReturn([new NotNull()]);

        $result = $this->helper->restoreRuntimeConstraints($this->dtoClass(), $prepared);

        static::assertCount(1, $result->constraints);
        static::assertInstanceOf(NotNull::class, $result->constraints[0]);
    }

    public function testRestoreVirtualConstraints(): void
    {
        $prepared = new MainDto(
            fields: [
                'entity' => new Property(
                    'entity',
                    Type::object(\stdClass::class),
                    virtual: [
                        'id' => new Property('id', Type::int(), requiresRuntimeResolve: true),
                    ]
                ),
            ],
            childRequiresRuntimeResolve: true
        );

        $this->getMockedService(Reflector::class)
            ->expects(static::once())
            ->method('reflectVirtualConstraints')
            ->with($this->dtoClass(), 'entity', 'id')
            ->willReturn([new NotNull()]);

        $result = $this->helper->restoreRuntimeConstraints($this->dtoClass(), $prepared);

        $entity = $result->fields['entity'];
        assert($entity instanceof Property);
        $id = $entity->virtual['id'];
        assert($id instanceof Property);

        static::assertCount(1, $id->constraints);
        static::assertInstanceOf(NotNull::class, $id->constraints[0]);
        static::assertFalse($id->requiresRuntimeResolve);
    }

    public function testRestoreDtoConstraints(): void
    {
        $prepared = new MainDto(
            fields: [
                'child' => new Dto('child', Type::object(AbstractDto::class), requiresRuntimeResolve: true),
            ],
            childRequiresRuntimeResolve: true
        );

        $this->getMockedService(Reflector::class)
            ->expects(static::once())
            ->method('reflectPropertyConstraints')
            ->with($this->dtoClass(), 'child')
            ->willReturn([new NotNull()]);

        $result = $this->helper->restoreRuntimeConstraints($this->dtoClass(), $prepared);

        static::assertCount(1, $result->fields['child']->constraints);
        static::assertInstanceOf(NotNull::class, $result->fields['child']->constraints[0]);
        static::assertFalse($result->fields['child']->requiresRuntimeResolve);
    }

    public function testNoReflectionCallsWhenNothingFlagged(): void
    {
        $mainDto = new MainDto(
            fields: [
                'name' => new Property('name', Type::string(), constraints: [new NotNull()]),
            ]
        );

        $this->getMockedService(Reflector::class)
            ->expects(static::never())
            ->method('reflectClassConstraints');

        $result = $this->helper->restoreRuntimeConstraints($this->dtoClass(), $mainDto);

        static::assertSame($mainDto, $result);
    }

    /**
     * @return class-string<AbstractDto>
     */
    private function dtoClass(): string
    {
        /** @var class-string<AbstractDto> */
        return \DualMedia\DtoRequestBundle\Tests\Fixture\Dto\MiniDto::class;
    }
}
