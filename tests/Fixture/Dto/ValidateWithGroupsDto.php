<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Fixture\Dto;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Dto\Attribute\ValidateWithGroups;
use DualMedia\DtoRequestBundle\Tests\Fixture\Service\TestGroupProvider;
use Symfony\Component\Validator\Constraints as Assert;

#[ValidateWithGroups(static function (TestGroupProvider $provider, ValidateWithGroupsDto $dto): array {
    return $provider->resolve();
})]
class ValidateWithGroupsDto extends AbstractDto
{
    #[Assert\NotBlank(groups: ['strict'])]
    public string|null $name = null;
}
