<?php

namespace DualMedia\DtoRequestBundle\Tests\Fixtures\Model\Dto\ValidationContext;

use DualMedia\DtoRequestBundle\Constraints\MappedToPath;
use DualMedia\DtoRequestBundle\Model\AbstractDto;
use Symfony\Component\Validator\Constraints as Assert;

#[Assert\Expression(
    'this.value != null',
    message: 'Value must not be null'
)]
#[MappedToPath(
    'something',
    new Assert\Expression(
        'this.value != null',
        message: 'Subpathed value'
    )
)]
class SubDto extends AbstractDto
{
    #[Assert\NotBlank]
    public string|null $value = null;
}