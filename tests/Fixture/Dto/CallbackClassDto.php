<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Fixture\Dto;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * DTO with a closure-based Callback constraint at the class level.
 */
#[Assert\Callback(callback: static function (mixed $value, ExecutionContextInterface $context): void {
    if ($value instanceof CallbackClassDto && 'invalid' === $value->name) {
        $context->buildViolation('DTO is not valid.')
            ->atPath('name')
            ->addViolation();
    }
})]
class CallbackClassDto extends AbstractDto
{
    public string|null $name = null;

    public int|null $age = null;
}
