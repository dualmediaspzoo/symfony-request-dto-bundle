<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Feature\Resolver;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use DualMedia\DtoRequestBundle\Resolve\DtoResolver;
use DualMedia\DtoRequestBundle\Tests\Fixture\Dto\ScalarDto;
use DualMedia\DtoRequestBundle\Tests\Fixture\Dto\SimpleFindByDto;
use DualMedia\DtoRequestBundle\Tests\Fixture\Dto\SimpleFindDto;
use DualMedia\DtoRequestBundle\Tests\Fixture\Entity\SimpleEntity;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\KernelTestCase;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;

#[Group('feature')]
#[Group('resolver')]
class VisitedTrackingTest extends KernelTestCase
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

    public function testGetVisitedReturnsExactlyTheReceivedScalarFields(): void
    {
        $dto = $this->resolver->resolve(
            ScalarDto::class,
            new Request(request: [
                'intField' => '5',
                'stringField' => 'x',
            ])
        );

        $visited = $dto->getVisited();
        sort($visited);

        static::assertSame(['intField', 'stringField'], $visited);
    }

    public function testGetVisitedIsEmptyWhenNoFieldsReceived(): void
    {
        $dto = $this->resolver->resolve(
            ScalarDto::class,
            new Request()
        );

        static::assertSame([], $dto->getVisited());
    }

    public function testFindOneByVisitedVirtualPropertyMarksVirtualAndParentWhenInputSent(): void
    {
        $entity = $this->createEntity();

        $dto = $this->resolver->resolve(
            SimpleFindDto::class,
            new Request(request: ['inputId' => (string)$entity->getId()])
        );

        static::assertTrue($dto->visited('entity'), 'parent FindOneBy property must be visited when virtual got input');
        static::assertTrue(
            $dto->visitedVirtualProperty('entity', 'id'),
            'virtual `id` must be visited when its `inputId` was provided'
        );
    }

    public function testFindOneByNotVisitedWhenNoInput(): void
    {
        $dto = $this->resolver->resolve(
            SimpleFindDto::class,
            new Request()
        );

        static::assertFalse($dto->visited('entity'), 'parent must NOT be visited when no virtual input was sent');
        static::assertFalse(
            $dto->visitedVirtualProperty('entity', 'id'),
            'virtual must NOT be visited when its input was absent'
        );
    }

    public function testFindByVisitedVirtualPropertyMarksVirtualAndParentWhenInputSent(): void
    {
        $a = $this->createEntity();
        $b = $this->createEntity();

        $dto = $this->resolver->resolve(
            SimpleFindByDto::class,
            new Request(request: ['inputIds' => [(string)$a->getId(), (string)$b->getId()]])
        );

        static::assertTrue($dto->visited('entities'));
        static::assertTrue($dto->visitedVirtualProperty('entities', 'id'));
    }

    public function testFindByNotVisitedWhenNoInput(): void
    {
        $dto = $this->resolver->resolve(
            SimpleFindByDto::class,
            new Request()
        );

        static::assertFalse($dto->visited('entities'));
        static::assertFalse($dto->visitedVirtualProperty('entities', 'id'));
    }

    private function createEntity(): SimpleEntity
    {
        $entity = new SimpleEntity();
        $this->em->persist($entity);
        $this->em->flush();

        return $entity;
    }
}
