<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Unit;

use DualMedia\DtoRequestBundle\Util;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[CoversClass(Util::class)]
#[Group('unit')]
class UtilTest extends TestCase
{
    /**
     * @param list<string> $segments
     */
    #[DataProvider('provideBuildValidationPathCases')]
    public function testBuildValidationPath(
        array $segments,
        string $expected
    ): void {
        static::assertSame($expected, Util::buildValidationPath($segments));
    }

    /**
     * @return iterable<string, array{list<string>, string}>
     */
    public static function provideBuildValidationPathCases(): iterable
    {
        yield 'empty segments' => [[], ''];
        yield 'single segment' => [['field'], 'field'];
        yield 'dot-separated segments' => [['parent', 'child', 'field'], 'parent.child.field'];
        yield 'numeric index uses brackets' => [['children', '0', 'intField'], 'children[0].intField'];
        yield 'multiple numeric indices' => [['items', '0', 'sub', '1', 'val'], 'items[0].sub[1].val'];
        yield 'consecutive numeric indices' => [['data', '0', '1'], 'data[0][1]'];
        yield 'root numeric' => [['0'], '0'];
        yield 'two segments with index' => [['list', '5'], 'list[5]'];
    }
}
