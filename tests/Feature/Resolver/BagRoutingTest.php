<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Feature\Resolver;

use DualMedia\DtoRequestBundle\Metadata\Enum\BagEnum;
use DualMedia\DtoRequestBundle\Resolve\DtoResolver;
use DualMedia\DtoRequestBundle\Tests\Fixture\Dto\QueryBagDto;
use DualMedia\DtoRequestBundle\Tests\Fixture\Dto\ScalarDto;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\KernelTestCase;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;

#[Group('feature')]
#[Group('resolver')]
class BagRoutingTest extends KernelTestCase
{
    private DtoResolver $resolver;

    protected function setUp(): void
    {
        $this->resolver = static::getService(DtoResolver::class);
    }

    public function testQueryBagResolution(): void
    {
        $dto = $this->resolver->resolve(
            ScalarDto::class,
            new Request(query: [
                'intField' => '42',
                'stringField' => 'from-query',
            ]),
            BagEnum::Query
        );

        static::assertTrue($dto->isValid());
        static::assertSame(42, $dto->intField);
        static::assertSame('from-query', $dto->stringField);
    }

    public function testQueryBagIgnoresRequestBody(): void
    {
        $dto = $this->resolver->resolve(
            ScalarDto::class,
            new Request(
                query: ['intField' => '10'],
                request: ['intField' => '99', 'stringField' => 'from-body']
            ),
            BagEnum::Query
        );

        static::assertTrue($dto->isValid());
        static::assertSame(10, $dto->intField);
        static::assertNull($dto->stringField);
    }

    public function testRequestBagIgnoresQuery(): void
    {
        $dto = $this->resolver->resolve(
            ScalarDto::class,
            new Request(
                query: ['intField' => '10'],
                request: ['intField' => '99']
            ),
            BagEnum::Request
        );

        static::assertTrue($dto->isValid());
        static::assertSame(99, $dto->intField);
    }

    public function testPerPropertyBagOverride(): void
    {
        $dto = $this->resolver->resolve(
            QueryBagDto::class,
            new Request(
                query: ['page' => '2', 'search' => 'test'],
                request: ['bodyField' => 'from-body']
            ),
            BagEnum::Query
        );

        static::assertTrue($dto->isValid());
        static::assertSame(2, $dto->page);
        static::assertSame('test', $dto->search);
        static::assertSame('from-body', $dto->bodyField);
    }

    public function testPerPropertyBagOverrideIgnoresDefaultBag(): void
    {
        $dto = $this->resolver->resolve(
            QueryBagDto::class,
            new Request(
                query: ['page' => '1', 'search' => 'x', 'bodyField' => 'wrong-bag'],
                request: ['bodyField' => 'right-bag']
            ),
            BagEnum::Query
        );

        static::assertTrue($dto->isValid());
        static::assertSame('right-bag', $dto->bodyField);
    }

    public function testCookieBagResolution(): void
    {
        $dto = $this->resolver->resolve(
            ScalarDto::class,
            new Request(cookies: [
                'stringField' => 'from-cookie',
                'intField' => '7',
            ]),
            BagEnum::Cookies
        );

        static::assertTrue($dto->isValid());
        static::assertSame('from-cookie', $dto->stringField);
        static::assertSame(7, $dto->intField);
    }

    public function testAttributesBagResolution(): void
    {
        $request = new Request();
        $request->attributes->set('intField', '55');
        $request->attributes->set('stringField', 'from-attr');

        $dto = $this->resolver->resolve(
            ScalarDto::class,
            $request,
            BagEnum::Attributes
        );

        static::assertTrue($dto->isValid());
        static::assertSame(55, $dto->intField);
        static::assertSame('from-attr', $dto->stringField);
    }
}
