<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Fixture\Dto;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Dto\Attribute\Field;
use DualMedia\DtoRequestBundle\Dto\Attribute\FindOneBy;
use DualMedia\DtoRequestBundle\Dto\Attribute\WithErrorPath;
use DualMedia\DtoRequestBundle\Tests\Fixture\Entity\SimpleEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class ErrorPathFindDto extends AbstractDto
{
    #[FindOneBy]
    #[WithErrorPath('customError')]
    #[Field('id', 'inputId')]
    #[Assert\Callback(callback: static function (mixed $value, ExecutionContextInterface $context): void {
        if (null !== $value) {
            $context->buildViolation('Entity is not acceptable.')
                ->addViolation();
        }
    })]
    public SimpleEntity|null $entity = null;
}
