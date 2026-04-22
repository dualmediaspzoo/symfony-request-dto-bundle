<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Fixture\Dto;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Dto\Attribute\AsRoot;
use Symfony\Component\Validator\Constraints as Assert;

class RootPathEntityDto extends AbstractDto
{
    #[AsRoot]
    #[Assert\Valid]
    public PropertyAssertFindDto|null $child = null;
}
