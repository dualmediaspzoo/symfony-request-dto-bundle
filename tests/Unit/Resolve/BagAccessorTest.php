<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Unit\Resolve;

use DualMedia\DtoRequestBundle\Metadata\Enum\BagEnum;
use DualMedia\DtoRequestBundle\Resolve\BagAccessor;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(BagAccessor::class)]
#[Group('unit')]
class BagAccessorTest extends TestCase
{
    public function testGetFromQuery(): void
    {
        $request = new Request(query: ['foo' => 'bar']);
        $accessor = new BagAccessor($request);

        static::assertSame('bar', $accessor->get(BagEnum::Query, ['foo']));
    }

    public function testGetFromRequestBody(): void
    {
        $request = new Request(request: ['name' => 'John']);
        $accessor = new BagAccessor($request);

        static::assertSame('John', $accessor->get(BagEnum::Request, ['name']));
    }

    public function testGetNestedPath(): void
    {
        $request = new Request(query: ['parent' => ['child' => ['value' => 42]]]);
        $accessor = new BagAccessor($request);

        static::assertSame(42, $accessor->get(BagEnum::Query, ['parent', 'child', 'value']));
    }

    public function testGetMissingKeyReturnsNull(): void
    {
        $request = new Request();
        $accessor = new BagAccessor($request);

        static::assertNull($accessor->get(BagEnum::Query, ['nonexistent']));
    }

    public function testGetPartialPathReturnsNull(): void
    {
        $request = new Request(query: ['foo' => 'bar']);
        $accessor = new BagAccessor($request);

        static::assertNull($accessor->get(BagEnum::Query, ['foo', 'deep']));
    }

    public function testHasReturnsTrue(): void
    {
        $request = new Request(query: ['foo' => 'bar']);
        $accessor = new BagAccessor($request);

        static::assertTrue($accessor->has(BagEnum::Query, ['foo']));
    }

    public function testHasReturnsFalse(): void
    {
        $request = new Request();
        $accessor = new BagAccessor($request);

        static::assertFalse($accessor->has(BagEnum::Query, ['foo']));
    }

    public function testHasNestedPath(): void
    {
        $request = new Request(query: ['a' => ['b' => 'c']]);
        $accessor = new BagAccessor($request);

        static::assertTrue($accessor->has(BagEnum::Query, ['a', 'b']));
        static::assertFalse($accessor->has(BagEnum::Query, ['a', 'x']));
    }

    public function testGetFromCookies(): void
    {
        $request = new Request(cookies: ['session' => 'abc123']);
        $accessor = new BagAccessor($request);

        static::assertSame('abc123', $accessor->get(BagEnum::Cookies, ['session']));
    }

    public function testGetFromAttributes(): void
    {
        $request = new Request();
        $request->attributes->set('_route', 'home');
        $accessor = new BagAccessor($request);

        static::assertSame('home', $accessor->get(BagEnum::Attributes, ['_route']));
    }

    public function testCacheIsUsedOnSecondAccess(): void
    {
        $request = new Request(query: ['key' => 'val']);
        $accessor = new BagAccessor($request);

        // First access populates cache
        static::assertSame('val', $accessor->get(BagEnum::Query, ['key']));
        // Mutate the request after caching
        $request->query->set('key', 'changed');
        // Should still return cached value
        static::assertSame('val', $accessor->get(BagEnum::Query, ['key']));
    }

    public function testGetNullValueDistinguishedFromMissing(): void
    {
        $request = new Request(query: ['key' => null]);
        $accessor = new BagAccessor($request);

        static::assertTrue($accessor->has(BagEnum::Query, ['key']));
        static::assertNull($accessor->get(BagEnum::Query, ['key']));
    }
}
