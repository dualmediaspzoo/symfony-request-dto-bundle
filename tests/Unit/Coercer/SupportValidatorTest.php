<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Unit\Coercer;

use DualMedia\DtoRequestBundle\Coercer\BooleanCoercer;
use DualMedia\DtoRequestBundle\Coercer\IntegerCoercer;
use DualMedia\DtoRequestBundle\Coercer\Registry;
use DualMedia\DtoRequestBundle\Coercer\StringCoercer;
use DualMedia\DtoRequestBundle\Coercer\SupportValidator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\TypeInfo\Type;

#[CoversClass(SupportValidator::class)]
#[Group('unit')]
#[Group('coercer')]
class SupportValidatorTest extends TestCase
{
    private SupportValidator $validator;

    protected function setUp(): void
    {
        $locator = new ServiceLocator([
            'integer' => static fn () => new IntegerCoercer(),
            'string' => static fn () => new StringCoercer(),
            'boolean' => static fn () => new BooleanCoercer(),
        ]);

        $this->validator = new SupportValidator(new Registry($locator));
    }

    public function testSupportsInt(): void
    {
        static::assertSame('integer', $this->validator->supports(Type::int()));
    }

    public function testSupportsString(): void
    {
        static::assertSame('string', $this->validator->supports(Type::string()));
    }

    public function testSupportsBool(): void
    {
        static::assertSame('boolean', $this->validator->supports(Type::bool()));
    }

    public function testUnsupportedTypeReturnsNull(): void
    {
        static::assertNull($this->validator->supports(Type::object(\stdClass::class)));
    }

    public function testCollectionUnwrapsValueType(): void
    {
        $type = Type::list(Type::int());
        static::assertSame('integer', $this->validator->supports($type));
    }

    public function testCacheIsReusedOnSecondCall(): void
    {
        $this->validator->supports(Type::int());
        static::assertSame('string', $this->validator->supports(Type::string()));
    }
}
