<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Fixture\Dto;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Dto\Attribute\Path;

class ProfileDottedDto extends AbstractDto
{
    #[Path('profile.username')]
    public string|null $username = null;

    #[Path('profile.description')]
    public string|null $description = null;

    // Non-dotted field interleaved to exercise grouping continuity.
    public string|null $tag = null;

    #[Path('profile.dateOfBirth')]
    public string|null $dateOfBirth = null;
}
