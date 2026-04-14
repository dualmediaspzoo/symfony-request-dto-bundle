<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Fixture\Dto;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Dto\Attribute\Field;
use DualMedia\DtoRequestBundle\Dto\Attribute\FindBy;
use DualMedia\DtoRequestBundle\Tests\Fixture\Entity\SimpleEntity;
use DualMedia\DtoRequestBundle\Type\TypeUtils;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class PropertyAssertFindByDto extends AbstractDto
{
    /**
     * @var list<SimpleEntity>
     */
    #[FindBy]
    #[Field('id', 'inputIds', TypeUtils::LIST_INT)]
    #[Assert\Callback(callback: static function (mixed $value, ExecutionContextInterface $context): void {
        if ([] !== $value) {
            $context->buildViolation('Entity list is not acceptable.')
                ->addViolation();
        }
    })]
    public array $entities = [];
}
