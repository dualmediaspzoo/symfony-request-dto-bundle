<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Unit\Coercer;

use DualMedia\DtoRequestBundle\Coercer\UploadedFileCoercer;
use DualMedia\DtoRequestBundle\Metadata\Model\Property;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\TypeInfo\Type;
use Symfony\Component\Validator\Constraints\Type as TypeConstraint;

#[CoversClass(UploadedFileCoercer::class)]
#[Group('unit')]
#[Group('coercer')]
class UploadedFileCoercerTest extends TestCase
{
    private UploadedFileCoercer $coercer;

    protected function setUp(): void
    {
        $this->coercer = new UploadedFileCoercer();
    }

    #[DataProvider('provideCoercionCases')]
    public function testCoercion(
        mixed $input,
        mixed $expected
    ): void {
        $result = $this->coercer->coerce($this->property());
        static::assertSame($expected, ($result->coerce)($input));
    }

    /**
     * @return iterable<string, array{mixed, mixed}>
     */
    public static function provideCoercionCases(): iterable
    {
        yield 'string null doesn\'t become null' => ['null', 'null'];
        yield 'null passes through' => [null, null];
        yield 'int passes through' => [42, 42];
        yield 'string passes through' => ['hello', 'hello'];
    }

    public function testUploadedFilePassesThrough(): void
    {
        $file = new UploadedFile(
            tempnam(sys_get_temp_dir(), 'test'),
            'test.txt',
            'text/plain',
            null,
            true
        );

        $result = $this->coercer->coerce($this->property());
        static::assertSame($file, ($result->coerce)($file));
    }

    public function testConstraints(): void
    {
        $result = $this->coercer->coerce($this->property());
        static::assertCount(1, $result->constraints);
        static::assertInstanceOf(TypeConstraint::class, $result->constraints[0]);
    }

    public function testNoInnerResult(): void
    {
        $result = $this->coercer->coerce($this->property());
        static::assertNull($result->inner);
    }

    private function property(): Property
    {
        return new Property('test', Type::object(UploadedFile::class));
    }
}
