<?php

namespace DualMedia\DtoRequestBundle\Tests\Unit\Service\Entity;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use DualMedia\DtoRequestBundle\Service\Entity\QueryCreator;
use DualMedia\DtoRequestBundle\Service\Entity\ReferenceHelper;
use DualMedia\DtoRequestBundle\Service\Entity\TargetProviderService;
use DualMedia\DtoRequestBundle\Tests\Fixtures\Entity\TestEntity;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\TestWith;

#[Group('unit')]
#[Group('service')]
#[Group('entity')]
#[CoversClass(TargetProviderService::class)]
class TargetProviderServiceTest extends TestCase
{
    #[TestWith(['aaaa', true])]
    #[TestWith(['bbbb', false, false])]
    #[TestWith(['aaaa', false, false])]
    public function testSet(
        string $fqcn,
        bool $known,
        bool $addEm = true
    ): void {
        static::assertEquals(
            $known,
            $this->getService($this->createMock(EntityRepository::class), $addEm)
                ->setFqcn($fqcn) // @phpstan-ignore-line
        );
    }

    #[TestWith([['some' => 15, 'field' => 'yeet']])]
    #[TestWith([['some' => 5525, 'field' => 'yeewwwwwwt'], ['id' => 'desc']])]
    public function testComplex(
        array $fields,
        array|null $orderBy = null
    ): void {
        $repo = $this->createMock(EntityRepository::class);
        $builder = $this->createMock(QueryBuilder::class);

        $repo->expects(static::once())
            ->method('createQueryBuilder')
            ->with('entity')
            ->willReturn($builder);

        $service = $this->getService($repo);
        static::assertTrue($service->setFqcn(TestEntity::class));

        $callable = function (
            array $f,
            array|null $o,
            QueryBuilder $b
        ) use ($fields, $orderBy, $builder) {
            $this->assertEquals($fields, $f);
            $this->assertEquals($orderBy, $o);
            $this->assertEquals($builder, $b);
        };

        $service->findComplex($callable, $fields, $orderBy);
    }

    #[TestWith([['some' => 15, 'field' => 'yeet']])]
    #[TestWith([['some' => 5525, 'field' => 'yeeeewt'], ['id' => 'desc']])]
    public function testFindOneBy(
        array $fields,
        array|null $orderBy = null
    ): void {
        $repo = $this->createMock(EntityRepository::class);
        $mock = $this->createMock(TestEntity::class);

        $repo->expects(static::once())
            ->method('findOneBy')
            ->with($fields, $orderBy)
            ->willReturn($mock);

        $service = $this->getService($repo);
        static::assertTrue($service->setFqcn(TestEntity::class));

        static::assertEquals($mock, $service->findOneBy($fields, $orderBy));
    }

    #[TestWith([['some' => 15, 'field' => 'yeet']])]
    #[TestWith([['somee' => 5525, 'field' => 'yeeewt2'], ['id' => 'desc']])]
    #[TestWith([['somee' => 5525, 'field' => 'yeeewt2'], ['id' => 'desc'], 5, 15])]
    #[TestWith([['somee' => 5525, 'field' => 'yeeewt2'], ['id' => 'desc'], 0, 222])]
    public function testFindBy(
        array $fields,
        array|null $orderBy = null,
        int|null $limit = null,
        int|null $offset = null
    ): void {
        $repo = $this->createMock(EntityRepository::class);
        $mock = $this->createMock(TestEntity::class);

        $repo->expects(static::once())
            ->method('findBy')
            ->with($fields, $orderBy, $limit, $offset)
            ->willReturn([$mock]);

        $service = $this->getService($repo);
        static::assertTrue($service->setFqcn(TestEntity::class));

        static::assertEquals([$mock], $service->findBy($fields, $orderBy, $limit, $offset));
    }

    private function getService(
        EntityRepository $repository,
        bool $addEm = true
    ): TargetProviderService {
        $registry = $this->createMock(ManagerRegistry::class);

        $manager = $addEm ? $this->createMock(EntityManagerInterface::class) : $this->createMock(ObjectManager::class);
        $manager->method('getRepository')
            ->willReturn($repository);

        $registry->method('getManagerForClass')
            ->willReturn($manager);

        return new TargetProviderService(
            $registry,
            $this->createMock(QueryCreator::class),
            $this->createMock(ReferenceHelper::class)
        );
    }
}
