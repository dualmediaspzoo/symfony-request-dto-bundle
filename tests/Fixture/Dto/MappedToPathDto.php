<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Fixture\Dto;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Validator\MappedToPath;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[MappedToPath(
    'endsAt',
    new Assert\Callback(static function (MappedToPathDto $dto, ExecutionContextInterface $context): void {
        if (null === $dto->startsAt || null === $dto->endsAt) {
            return;
        }

        if ($dto->endsAt <= $dto->startsAt) {
            $context->buildViolation('endsAt must be greater than startsAt')
                ->addViolation();
        }
    })
)]
class MappedToPathDto extends AbstractDto
{
    public int|null $startsAt = null;

    public int|null $endsAt = null;
}
