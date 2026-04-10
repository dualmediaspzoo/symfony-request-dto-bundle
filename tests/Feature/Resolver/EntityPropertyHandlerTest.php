<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Feature\Resolver;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use DualMedia\DtoRequestBundle\Resolve\DtoResolver;
use DualMedia\DtoRequestBundle\Tests\Fixture\Dto\ConstrainedFindDto;
use DualMedia\DtoRequestBundle\Tests\Fixture\Dto\DynamicFieldFindDto;
use DualMedia\DtoRequestBundle\Tests\Fixture\Dto\LiteralFieldFindDto;
use DualMedia\DtoRequestBundle\Tests\Fixture\Dto\SimpleFindByDto;
use DualMedia\DtoRequestBundle\Tests\Fixture\Dto\SimpleFindDto;
use DualMedia\DtoRequestBundle\Tests\Fixture\Entity\SimpleEntity;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;

class EntityPropertyHandlerTest extends KernelTestCase
{
    private DtoResolver $resolver;
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        $this->resolver = static::getService(DtoResolver::class);
        $this->em = static::getService(EntityManagerInterface::class);

        $schemaTool = new SchemaTool($this->em);
        $schemaTool->createSchema(
            $this->em->getMetadataFactory()->getAllMetadata()
        );
    }

    protected function tearDown(): void
    {
        $schemaTool = new SchemaTool($this->em);
        $schemaTool->dropSchema(
            $this->em->getMetadataFactory()->getAllMetadata()
        );

        parent::tearDown();
    }

    public function testFindOneBySuccess(): void
    {
        $entity = $this->createEntity();

        $dto = $this->resolver->resolve(
            SimpleFindDto::class,
            new Request(request: [
                'inputId' => (string)$entity->getId(),
            ])
        );

        static::assertTrue($dto->isValid());
        static::assertInstanceOf(SimpleEntity::class, $dto->entity);
        static::assertEquals($entity->getId(), $dto->entity->getId());
    }

    public function testFindOneByNotFound(): void
    {
        $dto = $this->resolver->resolve(
            SimpleFindDto::class,
            new Request(request: [
                'inputId' => '99999',
            ])
        );

        static::assertTrue($dto->isValid());
        static::assertNull($dto->entity);
    }

    public function testFindBySuccess(): void
    {
        $entity1 = $this->createEntity();
        $entity2 = $this->createEntity();

        $dto = $this->resolver->resolve(
            SimpleFindByDto::class,
            new Request(request: [
                'inputIds' => [(string)$entity1->getId(), (string)$entity2->getId()],
            ])
        );

        static::assertTrue($dto->isValid());
        static::assertCount(2, $dto->entities);
        static::assertInstanceOf(SimpleEntity::class, $dto->entities[0]);
        static::assertInstanceOf(SimpleEntity::class, $dto->entities[1]);
    }

    public function testValidationFailure(): void
    {
        $dto = $this->resolver->resolve(
            ConstrainedFindDto::class,
            new Request(request: [
                'inputId' => '-5',
            ])
        );

        static::assertFalse($dto->isValid());
        static::assertNull($dto->entity);

        $violations = static::getConstraintViolationsMappedToPropertyPaths($dto->getConstraintViolationList());
        static::assertNotEmpty($violations['entity.inputId'] ?? []);
    }

    public function testMissingInputValidatesNull(): void
    {
        $dto = $this->resolver->resolve(
            ConstrainedFindDto::class,
            new Request(request: [])
        );

        static::assertFalse($dto->isValid());
        static::assertNull($dto->entity);

        $violations = static::getConstraintViolationsMappedToPropertyPaths($dto->getConstraintViolationList());
        static::assertNotEmpty($violations['entity.inputId'] ?? []);
    }

    public function testLiteralFieldFindSuccess(): void
    {
        $entity = $this->createEntity('literal-value');

        $dto = $this->resolver->resolve(
            LiteralFieldFindDto::class,
            new Request()
        );

        static::assertTrue($dto->isValid());
        static::assertInstanceOf(SimpleEntity::class, $dto->entity);
        static::assertEquals($entity->getId(), $dto->entity->getId());
    }

    public function testLiteralFieldFindNotFound(): void
    {
        $this->createEntity('other-name');

        $dto = $this->resolver->resolve(
            LiteralFieldFindDto::class,
            new Request()
        );

        static::assertTrue($dto->isValid());
        static::assertNull($dto->entity);
    }

    public function testDynamicFieldFindSuccess(): void
    {
        $entity = $this->createEntity('dynamic-resolved');

        $dto = $this->resolver->resolve(
            DynamicFieldFindDto::class,
            new Request()
        );

        static::assertTrue($dto->isValid());
        static::assertInstanceOf(SimpleEntity::class, $dto->entity);
        static::assertEquals($entity->getId(), $dto->entity->getId());
    }

    public function testDynamicFieldFindNotFound(): void
    {
        $this->createEntity('something-else');

        $dto = $this->resolver->resolve(
            DynamicFieldFindDto::class,
            new Request()
        );

        static::assertTrue($dto->isValid());
        static::assertNull($dto->entity);
    }

    private function createEntity(
        string|null $name = null
    ): SimpleEntity {
        $entity = new SimpleEntity();

        if (null !== $name) {
            $entity->setName($name);
        }

        $this->em->persist($entity);
        $this->em->flush();

        return $entity;
    }
}
