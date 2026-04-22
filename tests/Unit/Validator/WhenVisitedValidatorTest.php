<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Unit\Validator;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Validator\WhenVisited;
use DualMedia\DtoRequestBundle\Validator\WhenVisitedValidator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

#[CoversClass(WhenVisitedValidator::class)]
#[Group('unit')]
#[Group('validator')]
class WhenVisitedValidatorTest extends TestCase
{
    private WhenVisitedValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new WhenVisitedValidator();
    }

    public function testThrowsOnWrongConstraintType(): void
    {
        $context = $this->createMock(ExecutionContextInterface::class);
        $this->validator->initialize($context);

        $this->expectException(UnexpectedTypeException::class);
        $this->validator->validate('value', new NotBlank());
    }

    public function testThrowsWhenObjectIsNotAbstractDto(): void
    {
        $context = $this->createMock(ExecutionContextInterface::class);
        $context->method('getObject')->willReturn(new \stdClass());
        $context->method('getPropertyName')->willReturn('field');

        $this->validator->initialize($context);

        $this->expectException(UnexpectedTypeException::class);
        $this->validator->validate('value', new WhenVisited(new NotBlank()));
    }

    public function testSkipsWhenPropertyNameIsNull(): void
    {
        $dto = new class extends AbstractDto {
            public string $field = '';
        };

        $context = $this->createMock(ExecutionContextInterface::class);
        $context->method('getObject')->willReturn($dto);
        $context->method('getPropertyName')->willReturn(null);
        $context->expects(static::never())->method('getValidator');

        $this->validator->initialize($context);
        $this->validator->validate('value', new WhenVisited(new NotBlank()));
    }

    public function testSkipsWhenPropertyNotVisited(): void
    {
        $dto = new class extends AbstractDto {
            public string $field = '';
        };

        $context = $this->createMock(ExecutionContextInterface::class);
        $context->method('getObject')->willReturn($dto);
        $context->method('getPropertyName')->willReturn('field');
        $context->expects(static::never())->method('getValidator');

        $this->validator->initialize($context);
        $this->validator->validate('value', new WhenVisited(new NotBlank()));
    }

    public function testDelegatesValidationWhenVisited(): void
    {
        $dto = new class extends AbstractDto {
            public string $field = '';
        };
        $dto->visit('field');

        $innerConstraint = new NotBlank();
        $constraint = new WhenVisited($innerConstraint);

        $innerValidator = $this->createMock(\Symfony\Component\Validator\Validator\ContextualValidatorInterface::class);
        $innerValidator->expects(static::once())
            ->method('validate')
            ->with('', $innerConstraint);

        $symfonyValidator = $this->createMock(\Symfony\Component\Validator\Validator\ValidatorInterface::class);
        $symfonyValidator->method('inContext')
            ->willReturn($innerValidator);

        $context = $this->createMock(ExecutionContextInterface::class);
        $context->method('getObject')->willReturn($dto);
        $context->method('getPropertyName')->willReturn('field');
        $context->method('getValidator')->willReturn($symfonyValidator);

        $this->validator->initialize($context);
        $this->validator->validate('', $constraint);
    }
}
