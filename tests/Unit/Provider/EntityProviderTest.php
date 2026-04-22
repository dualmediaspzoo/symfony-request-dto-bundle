<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Unit\Provider;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use DualMedia\DoctrineQueryCreator\QueryCreator;
use DualMedia\DoctrineQueryCreator\ReferenceHelper;
use DualMedia\DtoRequestBundle\Metadata\Model\AsDoctrineReference;
use DualMedia\DtoRequestBundle\Metadata\Model\FindBy;
use DualMedia\DtoRequestBundle\Metadata\Model\Limit;
use DualMedia\DtoRequestBundle\Metadata\Model\Offset;
use DualMedia\DtoRequestBundle\Metadata\Model\OrderBy;
use DualMedia\DtoRequestBundle\Provider\EntityProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Pkly\ServiceMockHelperTrait;

#[CoversClass(EntityProvider::class)]
#[Group('unit')]
#[Group('provider')]
class EntityProviderTest extends TestCase
{
    use ServiceMockHelperTrait;

    private EntityProvider $provider;

    protected function setUp(): void
    {
        $this->provider = $this->createRealMockedServiceInstance(
            EntityProvider::class,
            ['class' => \stdClass::class]
        );
    }

    public function testFindOneBy(): void
    {
        $criteria = ['id' => 1];
        $entity = new \stdClass();

        $this->getMockedService(EntityRepository::class)
            ->expects(static::once())
            ->method('findOneBy')
            ->with($criteria, [])
            ->willReturn($entity);

        $result = $this->provider->find($criteria, [new FindBy(false)]);
        static::assertSame($entity, $result);
    }

    public function testFindBy(): void
    {
        $criteria = ['status' => 'active'];
        $entities = [new \stdClass(), new \stdClass()];

        $this->getMockedService(EntityRepository::class)
            ->expects(static::once())
            ->method('findBy')
            ->with($criteria, [], null, null)
            ->willReturn($entities);

        $result = $this->provider->find($criteria, [new FindBy(true)]);
        static::assertSame($entities, $result);
    }

    public function testFindByWithOrderByLimitOffset(): void
    {
        $criteria = ['status' => 'active'];

        $this->getMockedService(EntityRepository::class)
            ->expects(static::once())
            ->method('findBy')
            ->with($criteria, ['name' => 'ASC', 'id' => 'DESC'], 10, 5)
            ->willReturn([]);

        $this->provider->find($criteria, [
            new FindBy(true),
            new OrderBy('name', 'ASC'),
            new OrderBy('id', 'DESC'),
            new Limit(10),
            new Offset(5),
        ]);
    }

    public function testFindOneByWithOrderBy(): void
    {
        $criteria = ['id' => 1];

        $this->getMockedService(EntityRepository::class)
            ->expects(static::once())
            ->method('findOneBy')
            ->with($criteria, ['name' => 'ASC']);

        $this->provider->find($criteria, [
            new FindBy(false),
            new OrderBy('name', 'ASC'),
        ]);
    }

    public function testFindAsReference(): void
    {
        $criteria = ['id' => 1];
        $qb = $this->createMock(QueryBuilder::class);
        $builtQb = $this->createMock(QueryBuilder::class);
        $references = [new \stdClass()];

        $this->getMockedService(EntityRepository::class)
            ->method('createQueryBuilder')
            ->with('entity')
            ->willReturn($qb);

        $this->getMockedService(QueryCreator::class)
            ->expects(static::once())
            ->method('build')
            ->with($qb, 'entity', $criteria, [], null, null)
            ->willReturn($builtQb);

        $this->getMockedService(ReferenceHelper::class)
            ->expects(static::once())
            ->method('resolve')
            ->with($builtQb, \stdClass::class)
            ->willReturn($references);

        $result = $this->provider->find($criteria, [
            new FindBy(false),
            new AsDoctrineReference(),
        ]);

        static::assertSame($references, $result);
    }

    public function testFindByAsReferenceMany(): void
    {
        $criteria = ['status' => 'active'];
        $qb = $this->createMock(QueryBuilder::class);
        $builtQb = $this->createMock(QueryBuilder::class);
        $references = [[new \stdClass()]];

        $this->getMockedService(EntityRepository::class)
            ->method('createQueryBuilder')
            ->with('entity')
            ->willReturn($qb);

        $this->getMockedService(QueryCreator::class)
            ->method('build')
            ->willReturn($builtQb);

        $this->getMockedService(ReferenceHelper::class)
            ->method('resolve')
            ->willReturn($references);

        $result = $this->provider->find($criteria, [
            new FindBy(true),
            new AsDoctrineReference(),
        ]);

        static::assertSame($references[0], $result);
    }
}
