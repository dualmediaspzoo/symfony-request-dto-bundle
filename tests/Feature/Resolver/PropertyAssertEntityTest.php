<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Feature\Resolver;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use DualMedia\DtoRequestBundle\Resolve\DtoResolver;
use DualMedia\DtoRequestBundle\Tests\Fixture\Dto\ErrorPathFindDto;
use DualMedia\DtoRequestBundle\Tests\Fixture\Dto\ParentEntityDto;
use DualMedia\DtoRequestBundle\Tests\Fixture\Dto\ParentEntityListDto;
use DualMedia\DtoRequestBundle\Tests\Fixture\Dto\PropertyAssertFindByDto;
use DualMedia\DtoRequestBundle\Tests\Fixture\Dto\PropertyAssertFindDto;
use DualMedia\DtoRequestBundle\Tests\Fixture\Entity\SimpleEntity;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\KernelTestCase;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;

/**
 * Verifies that Phase 4 violation paths are remapped from PHP property
 * names to user-facing input field names.
 */
#[Group('feature')]
#[Group('resolver')]
class PropertyAssertEntityTest extends KernelTestCase
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

    public function testFindOneByPropertyAssertViolationPath(): void
    {
        $entity = $this->createEntity();

        $dto = $this->resolver->resolve(
            PropertyAssertFindDto::class,
            new Request(request: [
                'inputId' => (string)$entity->getId(),
            ])
        );

        static::assertFalse($dto->isValid());
        static::assertInstanceOf(SimpleEntity::class, $dto->entity);

        $violations = static::getConstraintViolationsMappedToPropertyPaths($dto->getConstraintViolationList());

        static::assertArrayHasKey('inputId', $violations);
        static::assertArrayNotHasKey('entity', $violations);
        static::assertCount(1, $violations['inputId']);
        static::assertSame('Entity is not acceptable.', $violations['inputId'][0]->getMessage());
    }

    public function testFindByPropertyAssertViolationPath(): void
    {
        $entity1 = $this->createEntity();
        $entity2 = $this->createEntity();

        $dto = $this->resolver->resolve(
            PropertyAssertFindByDto::class,
            new Request(request: [
                'inputIds' => [(string)$entity1->getId(), (string)$entity2->getId()],
            ])
        );

        static::assertFalse($dto->isValid());
        static::assertNotEmpty($dto->entities);

        $violations = static::getConstraintViolationsMappedToPropertyPaths($dto->getConstraintViolationList());

        static::assertArrayHasKey('inputIds', $violations);
        static::assertArrayNotHasKey('entities', $violations);
        static::assertCount(1, $violations['inputIds']);
        static::assertSame('Entity list is not acceptable.', $violations['inputIds'][0]->getMessage());
    }

    public function testWithErrorPathOverride(): void
    {
        $entity = $this->createEntity();

        $dto = $this->resolver->resolve(
            ErrorPathFindDto::class,
            new Request(request: [
                'inputId' => (string)$entity->getId(),
            ])
        );

        static::assertFalse($dto->isValid());
        static::assertInstanceOf(SimpleEntity::class, $dto->entity);

        $violations = static::getConstraintViolationsMappedToPropertyPaths($dto->getConstraintViolationList());

        static::assertArrayHasKey('customError', $violations);
        static::assertArrayNotHasKey('entity', $violations);
        static::assertArrayNotHasKey('inputId', $violations);
        static::assertCount(1, $violations['customError']);
        static::assertSame('Entity is not acceptable.', $violations['customError'][0]->getMessage());
    }

    public function testNestedSingleDtoEntityViolationPath(): void
    {
        $entity = $this->createEntity();

        $dto = $this->resolver->resolve(
            ParentEntityDto::class,
            new Request(request: [
                'child' => [
                    'inputId' => (string)$entity->getId(),
                ],
            ])
        );

        static::assertFalse($dto->isValid());
        static::assertNotNull($dto->child);
        static::assertInstanceOf(SimpleEntity::class, $dto->child->entity);

        $violations = static::getConstraintViolationsMappedToPropertyPaths($dto->getConstraintViolationList());

        static::assertArrayHasKey('child.inputId', $violations);
        static::assertArrayNotHasKey('child.entity', $violations);
        static::assertSame('Entity is not acceptable.', $violations['child.inputId'][0]->getMessage());
    }

    public function testNestedListDtoEntityViolationPath(): void
    {
        $entity = $this->createEntity();

        $dto = $this->resolver->resolve(
            ParentEntityListDto::class,
            new Request(request: [
                'children' => [
                    ['inputId' => (string)$entity->getId()],
                ],
            ])
        );

        static::assertFalse($dto->isValid());
        static::assertNotEmpty($dto->children);
        static::assertInstanceOf(SimpleEntity::class, $dto->children[0]->entity);

        $violations = static::getConstraintViolationsMappedToPropertyPaths($dto->getConstraintViolationList());

        static::assertArrayHasKey('children[0].inputId', $violations);
        static::assertArrayNotHasKey('children[0].entity', $violations);
        static::assertSame('Entity is not acceptable.', $violations['children[0].inputId'][0]->getMessage());
    }

    private function createEntity(): SimpleEntity
    {
        $entity = new SimpleEntity();
        $this->em->persist($entity);
        $this->em->flush();

        return $entity;
    }
}
