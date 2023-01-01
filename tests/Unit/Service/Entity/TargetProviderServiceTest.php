<?php

namespace DualMedia\DtoRequestBundle\Tests\Unit\Service\Entity;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use DualMedia\DtoRequestBundle\Service\Entity\TargetProviderService;
use DualMedia\DtoRequestBundle\Tests\Fixtures\Entity\TestEntity;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\TestCase;

class TargetProviderServiceTest extends TestCase
{
    /**
     * @testWith ["aaaa", true]
     *           ["bbbb", false, false, false]
     *           ["aaa", false, false]
     *           ["aaaa", false, true, false]
     */
    public function testSet(
        string $fqcn,
        bool $known,
        bool $hasRepo = true,
        bool $addEm = true
    ): void {
        $this->assertEquals(
            $known,
            $this->getService($hasRepo ? $this->createMock(EntityRepository::class) : null, $addEm)
                ->setFqcn($fqcn) // @phpstan-ignore-line
        );
    }

    /**
     * @testWith [{"some": 15, "field": "yeet"}]
     *           [{"some": 5525, "field": "yeewwwwwwwt"}, {"id": "desc"}]
     */
    public function testComplex(
        array $fields,
        array|null $orderBy = null
    ): void {
        $repo = $this->createMock(EntityRepository::class);
        $builder = $this->createMock(QueryBuilder::class);

        $repo->expects($this->once())
            ->method('createQueryBuilder')
            ->with('entity')
            ->willReturn($builder);

        $service = $this->getService($repo);
        $this->assertTrue($service->setFqcn(TestEntity::class));

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

    /**
     * @testWith [{"some": 15, "field": "yeet"}]
     *           [{"some": 5525, "field": "yeewwwwwwwt"}, {"id": "desc"}]
     */
    public function testFindOneBy(
        array $fields,
        array|null $orderBy = null
    ): void {
        $repo = $this->createMock(EntityRepository::class);
        $mock = $this->createMock(TestEntity::class);

        $repo->expects($this->once())
            ->method('findOneBy')
            ->with($fields, $orderBy)
            ->willReturn($mock);

        $service = $this->getService($repo);
        $this->assertTrue($service->setFqcn(TestEntity::class));

        $this->assertEquals($mock, $service->findOneBy($fields, $orderBy));
    }

    /**
     * @testWith [{"some": 15, "field": "yeet"}]
     *           [{"some": 5525, "field": "yeewwwwwwwt"}, {"id": "desc"}]
     *           [{"some": 5525, "field": "yeewwwwwwwt"}, {"id": "desc"}, 5, 15]
     *           [{"some": 5525, "field": "yeewwwwwwwt"}, {"id": "desc"}, 0, 222]
     */
    public function testFindBy(
        array $fields,
        array|null $orderBy = null,
        ?int $limit = null,
        ?int $offset = null
    ): void {
        $repo = $this->createMock(EntityRepository::class);
        $mock = $this->createMock(TestEntity::class);

        $repo->expects($this->once())
            ->method('findBy')
            ->with($fields, $orderBy, $limit, $offset)
            ->willReturn($mock);

        $service = $this->getService($repo);
        $this->assertTrue($service->setFqcn(TestEntity::class));

        $this->assertEquals($mock, $service->findBy($fields, $orderBy, $limit, $offset));
    }

    private function getService(
        EntityRepository|null $repository,
        bool $addEm = true
    ): TargetProviderService {
        $registry = $this->createMock(ManagerRegistry::class);

        $manager = $this->createMock(EntityManagerInterface::class);
        $manager->method('getRepository')
            ->willReturn($repository);

        $registry->method('getManagerForClass')
            ->willReturn($addEm ? $manager : null);

        return new TargetProviderService($registry);
    }
}
