<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Feature\Resolver;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use DualMedia\DtoRequestBundle\Resolve\DtoResolver;
use DualMedia\DtoRequestBundle\Tests\Fixture\Dto\ConstrainedFindByDto;
use DualMedia\DtoRequestBundle\Tests\Fixture\Entity\SimpleEntity;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\KernelTestCase;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;

#[Group('feature')]
#[Group('resolver')]
class ConstrainedFindByDtoTest extends KernelTestCase
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

    public function testValidCollectionLoadsEntities(): void
    {
        $e1 = $this->createEntity();
        $e2 = $this->createEntity();

        $dto = $this->resolver->resolve(
            ConstrainedFindByDto::class,
            new Request(request: [
                'inputIds' => [(string)$e1->getId(), (string)$e2->getId()],
            ])
        );

        static::assertTrue($dto->isValid());
        static::assertCount(2, $dto->entities);
    }

    public function testNegativeIdViolationPointsToElement(): void
    {
        $dto = $this->resolver->resolve(
            ConstrainedFindByDto::class,
            new Request(request: [
                'inputIds' => ['1', '2', '-3'],
            ])
        );

        static::assertFalse($dto->isValid());
        static::assertSame([], $dto->entities);

        $violations = static::getConstraintViolationsMappedToPropertyPaths($dto->getConstraintViolationList());
        // Violation on the third element should include the index
        static::assertArrayHasKey('inputIds[2]', $violations);
        // No violation on valid elements
        static::assertArrayNotHasKey('inputIds[0]', $violations);
        static::assertArrayNotHasKey('inputIds[1]', $violations);
    }

    public function testTypeViolationOnElement(): void
    {
        $dto = $this->resolver->resolve(
            ConstrainedFindByDto::class,
            new Request(request: [
                'inputIds' => ['1', 'abc'],
            ])
        );

        static::assertFalse($dto->isValid());

        $violations = static::getConstraintViolationsMappedToPropertyPaths($dto->getConstraintViolationList());
        static::assertArrayHasKey('inputIds[1]', $violations);
        static::assertArrayNotHasKey('inputIds[0]', $violations);
    }

    public function testAllElementsInvalid(): void
    {
        $dto = $this->resolver->resolve(
            ConstrainedFindByDto::class,
            new Request(request: [
                'inputIds' => ['-1', '-2'],
            ])
        );

        static::assertFalse($dto->isValid());

        $violations = static::getConstraintViolationsMappedToPropertyPaths($dto->getConstraintViolationList());
        static::assertArrayHasKey('inputIds[0]', $violations);
        static::assertArrayHasKey('inputIds[1]', $violations);
    }

    public function testEmptyCollectionPassesValidation(): void
    {
        $dto = $this->resolver->resolve(
            ConstrainedFindByDto::class,
            new Request(request: [
                'inputIds' => [],
            ])
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
