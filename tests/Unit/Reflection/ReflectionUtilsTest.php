<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Unit\Reflection;

use DualMedia\DtoRequestBundle\Reflection\ReflectionUtils;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[CoversClass(ReflectionUtils::class)]
#[Group('unit')]
#[Group('reflection')]
class ReflectionUtilsTest extends TestCase
{
    public function testExtractShortDescriptionReturnsNullWhenNoDocComment(): void
    {
        static::assertNull(ReflectionUtils::extractShortDescription(false));
    }

    public function testExtractShortDescriptionReturnsNullWhenDocCommentIsEmpty(): void
    {
        static::assertNull(ReflectionUtils::extractShortDescription("/**\n */"));
    }

    public function testExtractShortDescriptionHandlesSingleLine(): void
    {
        $doc = '/** Single line summary. */';

        static::assertSame('Single line summary.', ReflectionUtils::extractShortDescription($doc));
    }

    public function testExtractShortDescriptionHandlesMultilineWithParagraphBreak(): void
    {
        $doc = <<<'DOC'
            /**
             * Description of a thing.
             *
             * It can also be multiline.
             * So it displays more shit.
             */
            DOC;

        static::assertSame(
            "Description of a thing.  \n\nIt can also be multiline.  \nSo it displays more shit.",
            ReflectionUtils::extractShortDescription($doc)
        );
    }

    public function testExtractShortDescriptionStopsAtFirstTag(): void
    {
        $doc = <<<'DOC'
            /**
             * A thing.
             *
             * With details.
             *
             * @see SomeClass
             * @param string $foo
             */
            DOC;

        static::assertSame(
            "A thing.  \n\nWith details.",
            ReflectionUtils::extractShortDescription($doc)
        );
    }

    public function testExtractShortDescriptionPreservesSingleLineBreaks(): void
    {
        $doc = <<<'DOC'
            /**
             * Line one.
             * Line two.
             * Line three.
             */
            DOC;

        static::assertSame(
            "Line one.  \nLine two.  \nLine three.",
            ReflectionUtils::extractShortDescription($doc)
        );
    }

    public function testExtractShortDescriptionReturnsNullWhenOnlyTags(): void
    {
        $doc = <<<'DOC'
            /**
             * @see SomeClass
             * @internal
             */
            DOC;

        static::assertNull(ReflectionUtils::extractShortDescription($doc));
    }
}