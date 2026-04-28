<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Fixture\Dto;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Dto\Attribute\ValidateWithGroups;
use DualMedia\DtoRequestBundle\Tests\Fixture\Service\TestExtraGroupProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

#[ValidateWithGroups(static function (TestExtraGroupProvider $provider, ExtraGroupValidatedDto $dto, Request $request): array {
    return $provider->resolve();
})]
class ExtraGroupValidatedDto extends AbstractDto
{
    #[Assert\Positive(groups: ['extra'])]
    #[Assert\NotNull]
    public int|null $value = null;
}
