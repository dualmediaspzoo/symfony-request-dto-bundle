<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Feature\Resolver;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use DualMedia\DtoRequestBundle\Resolve\DtoResolver;
use DualMedia\DtoRequestBundle\Tests\Fixture\Dto\AsDoctrineReferenceManyDto;
use DualMedia\DtoRequestBundle\Tests\Fixture\Dto\AsDoctrineReferenceOneDto;
use DualMedia\DtoRequestBundle\Tests\Fixture\Entity\SimpleEntity;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\KernelTestCase;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;

#[Group('feature')]
#[Group('resolver')]
class AsDoctrineReferenceDtoTest extends KernelTestCase
{
    private DtoResolver $resolver;

    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        $this->resolver = static::getService(DtoResolver::class);
        $this->em = static::getService(EntityManagerInterface::class);

        new SchemaTool($this->em)->createSchema(
            $this->em->getMetadataFactory()->getAllMetadata()
        );
    }

    protected function tearDown(): void
    {
        new SchemaTool($this->em)->dropSchema(
            $this->em->getMetadataFactory()->getAllMetadata()
        );

        parent::tearDown();
    }

    public function testFindOneByAsReferenceReturnsSingleEntity(): void
    {
        $entity = $this->createEntity();

        $dto = $this->resolver->resolve(
            AsDoctrineReferenceOneDto::class,
            new Request(request: ['inputId' => (string)$entity->getId()])
        );

        static::assertTrue($dto->isValid(), (string)$dto->getConstraintViolationList());
        static::assertInstanceOf(SimpleEntity::class, $dto->entity);
        static::assertSame($entity->getId(), $dto->entity->getId());
    }

    public function testFindOneByAsReferenceReturnsNullWhenMissing(): void
    {
        $dto = $this->resolver->resolve(
            AsDoctrineReferenceOneDto::class,
            new Request(request: ['inputId' => '99999'])
        );

        static::assertTrue($dto->isValid());
        static::assertNull($dto->entity);
    }

    public function testFindByAsReferenceReturnsListOfEntities(): void
    {
        $a = $this->createEntity();
        $b = $this->createEntity();

        $dto = $this->resolver->resolve(
            AsDoctrineReferenceManyDto::class,
            new Request(request: [
                'inputIds' => [(string)$a->getId(), (string)$b->getId()],
            ])
        );

        static::assertTrue($dto->isValid(), (string)$dto->getConstraintViolationList());
        static::assertCount(2, $dto->entities);
        $ids = array_map(static fn (SimpleEntity $e): int|null => $e->getId(), $dto->entities);
        sort($ids);
        static::assertSame([$a->getId(), $b->getId()], $ids);

        foreach ($dto->entities as $entity) {
            static::assertInstanceOf(SimpleEntity::class, $entity);
        }
    }

    public function testFindByAsReferenceReturnsEmptyListForUnknownIds(): void
    {
        $dto = $this->resolver->resolve(
            AsDoctrineReferenceManyDto::class,
            new Request(request: ['inputIds' => ['99998', '99999']])
        );

        static::assertTrue($dto->isValid());
        static::assertSame([], $dto->entities);
    }

    private function createEntity(): SimpleEntity
    {
        $entity = new SimpleEntity();
        $this->em->persist($entity);
        $this->em->flush();

        return $entity;
    }
}
