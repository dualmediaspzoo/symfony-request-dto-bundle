<?php

namespace DualMedia\DtoRequestBundle\Tests\Unit\Constraint;

use DualMedia\DtoRequestBundle\Constraint\WhenVisited;
use DualMedia\DtoRequestBundle\Constraint\WhenVisitedValidator;
use DualMedia\DtoRequestBundle\Model\AbstractDto;
use DualMedia\DtoRequestBundle\Tests\PHPUnit\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Validator\ContextualValidatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Group('unit')]
#[Group('constraints')]
#[CoversClass(WhenVisitedValidator::class)]
class WhenVisitedValidatorTest extends TestCase
{
    private WhenVisitedValidator $validator;
    private ExecutionContextInterface&MockObject $context;

    protected function setUp(): void
    {
        $this->context = $this->createMock(ExecutionContextInterface::class);
        $this->validator = new WhenVisitedValidator();
        $this->validator->initialize($this->context);
    }

    public static function provideValidateCases(): iterable
    {
        yield ['test', 'test', [new NotNull()]];
        yield ['test', 'test', new NotNull()];
        yield [null, 'test', [new NotNull()]];
        yield [123, 'price', [new NotNull(), new NotBlank()]];
        yield ['test', 'test', [new NotNull()], false];
        yield [123, 'price', [new NotNull(), new NotBlank()], false];
        yield ['test', 'test', [new NotNull()], true, false];
        yield ['test', 'test', [new NotNull()], true, true, false];
    }

    /**
     * @param list<Constraint>|Constraint $constraints
     */
    #[DataProvider('provideValidateCases')]
    public function testValidate(
        string|int|null $value,
        string $propertyName,
        array|Constraint $constraints,
        bool $visited = true,
        bool $hasObject = true,
        bool $hasPropertyName = true
    ): void {
        $constraint = new WhenVisited($constraints);

        $contextualValidatorInterface = $this->createMock(ContextualValidatorInterface::class);
        $contextualValidatorInterface
            ->expects(static::exactly((int)($hasObject && $visited && $hasPropertyName)))
            ->method('validate')
            ->with($value, $constraint->constraints);

        $validator = $this->createMock(ValidatorInterface::class);
        $validator
            ->expects(static::exactly((int)($hasObject && $visited && $hasPropertyName)))
            ->method('inContext')
            ->with($this->context)
            ->willReturn($contextualValidatorInterface);

        $this->context->expects(static::exactly((int)($hasObject && $visited && $hasPropertyName)))
            ->method('getValidator')
            ->willReturn($validator);

        $this->context->expects(static::exactly((int)$hasObject))
            ->method('getPropertyName')
            ->willReturn($hasPropertyName ? $propertyName : null);

        $object = $this->createMock(AbstractDto::class);
        $object
            ->expects(static::exactly((int)($hasObject && $hasPropertyName)))
            ->method('visited')
            ->with($propertyName)
            ->willReturn($visited);

        $this->context->expects(static::once())
            ->method('getObject')
            ->willReturn($hasObject ? $object : null);

        if (!$hasObject) {
            $this->expectException(UnexpectedTypeException::class);
        }

        $this->validator->validate($value, $constraint);
    }
}
