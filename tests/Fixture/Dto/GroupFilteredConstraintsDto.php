<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Fixture\Dto;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use Symfony\Component\Validator\Constraints as Assert;

class GroupFilteredConstraintsDto extends AbstractDto
{
    // Default group → must surface in OpenAPI as required + minLength.
    #[Assert\NotBlank]
    #[Assert\Length(min: 3)]
    public string|null $defaultField = null;

    // Explicit non-default group → must be ignored entirely.
    #[Assert\NotBlank(groups: ['admin'])]
    #[Assert\Length(min: 5, groups: ['admin'])]
    public string|null $adminOnlyField = null;

    // Explicit Default group plus extra → kept.
    #[Assert\NotBlank(groups: ['Default', 'admin'])]
    #[Assert\Length(min: 7, groups: ['Default', 'admin'])]
    public string|null $sharedField = null;
}
