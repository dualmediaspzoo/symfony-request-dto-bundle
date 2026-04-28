<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Unit;

use DualMedia\DtoRequestBundle\Util;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\GroupSequence;

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
        yield 'root numeric' => [['0'], '[0]'];
        yield 'two segments with index' => [['list', '5'], 'list[5]'];
    }

    public function testMergeDefaultGroupBareDefaultStringReturnedUnchanged(): void
    {
        static::assertSame(Constraint::DEFAULT_GROUP, Util::mergeDefaultGroup(Constraint::DEFAULT_GROUP));
    }

    public function testMergeDefaultGroupBareCustomStringWrappedWithDefault(): void
    {
        static::assertSame(['extra', Constraint::DEFAULT_GROUP], Util::mergeDefaultGroup('extra'));
    }

    public function testMergeDefaultGroupListMissingDefaultGetsDefaultAppended(): void
    {
        static::assertSame(['extra', Constraint::DEFAULT_GROUP], Util::mergeDefaultGroup(['extra']));
    }

    public function testMergeDefaultGroupListContainingDefaultIsUnchanged(): void
    {
        $input = ['extra', Constraint::DEFAULT_GROUP, 'admin'];
        static::assertSame($input, Util::mergeDefaultGroup($input));
    }

    public function testMergeDefaultGroupListWithSequenceCoveringDefaultIsUnchanged(): void
    {
        $sequence = new GroupSequence(['extra', Constraint::DEFAULT_GROUP]);
        $input = [$sequence];

        $result = Util::mergeDefaultGroup($input);

        static::assertSame($input, $result);
    }

    public function testMergeDefaultGroupListWithSequenceMissingDefaultGetsDefaultAppendedToOuterList(): void
    {
        $sequence = new GroupSequence(['extra', 'admin']);

        $result = Util::mergeDefaultGroup([$sequence]);

        static::assertIsArray($result);
        static::assertCount(2, $result);
        static::assertSame($sequence, $result[0]);
        static::assertSame(Constraint::DEFAULT_GROUP, $result[1]);
    }

    public function testMergeDefaultGroupSequenceContainingDefaultReturnedUnchanged(): void
    {
        $sequence = new GroupSequence([Constraint::DEFAULT_GROUP, 'extra']);

        static::assertSame($sequence, Util::mergeDefaultGroup($sequence));
    }

    public function testMergeDefaultGroupSequenceMissingDefaultGetsDefaultAppendedInsideSequence(): void
    {
        $sequence = new GroupSequence(['extra', 'admin']);

        $result = Util::mergeDefaultGroup($sequence);

        static::assertInstanceOf(GroupSequence::class, $result);
        static::assertNotSame($sequence, $result, 'must return a fresh sequence to keep the original immutable from the caller');
        static::assertSame(['extra', 'admin', Constraint::DEFAULT_GROUP], $result->groups);
    }

    public function testMergeDefaultGroupEmptyListGetsDefault(): void
    {
        static::assertSame([Constraint::DEFAULT_GROUP], Util::mergeDefaultGroup([]));
    }

    public function testMergeDefaultGroupSequenceWithNestedParallelStepCoveringDefaultIsUnchanged(): void
    {
        // Symfony allows a parallel step (sub-array) inside a GroupSequence;
        // Default living inside that nested step must be detected.
        $sequence = new GroupSequence([['extra', Constraint::DEFAULT_GROUP], 'admin']);

        static::assertSame($sequence, Util::mergeDefaultGroup($sequence));
    }

    public function testMergeDefaultGroupListWithNestedSubArrayCoveringDefaultIsUnchanged(): void
    {
        $input = [['extra', Constraint::DEFAULT_GROUP], 'admin'];

        static::assertSame($input, Util::mergeDefaultGroup($input)); // @phpstan-ignore-line
    }

    public function testMergeDefaultGroupSequenceWithDeeplyNestedSequenceCoveringDefaultIsUnchanged(): void
    {
        $inner = new GroupSequence([Constraint::DEFAULT_GROUP, 'extra']);
        $outer = new GroupSequence([$inner, 'admin']);

        static::assertSame($outer, Util::mergeDefaultGroup($outer));
    }
}
