<?php

namespace DualMedia\DtoRequestBundle\Tests\Fixtures\Model\Dto\ValidationContext;

use DualMedia\DtoRequestBundle\Constraints\MappedToPath;
use DualMedia\DtoRequestBundle\Model\AbstractDto;
use Symfony\Component\Validator\Constraints as Assert;

#[MappedToPath(
    'intVal',
    new Assert\Expression(
        'this.intVal != null',
        message: 'Expression failed, intVal is null'
    )
)]
class MappedToDto extends AbstractDto
{
    #[Assert\NotNull]
    #[Assert\GreaterThanOrEqual(15)]
    public int|null $intVal = null;
}
