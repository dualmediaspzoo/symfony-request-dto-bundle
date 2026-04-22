<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Fixture\Dto;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * DTO with a closure-based Callback constraint on a property.
 */
class CallbackPropertyDto extends AbstractDto
{
    #[Assert\Callback(callback: static function (mixed $value, ExecutionContextInterface $context): void {
        if ('invalid' === $value) {
            $context->buildViolation('Name is not valid.')
                ->addViolation();
        }
    })]
    public string|null $name = null;

    public int|null $age = null;
}
