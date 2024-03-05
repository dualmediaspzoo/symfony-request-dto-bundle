<?php

namespace DualMedia\DtoRequestBundle\Tests\Fixtures\Model\Dto\ValidationContext;

use DualMedia\DtoRequestBundle\Model\AbstractDto;
use Symfony\Component\Validator\Constraints as Assert;

#[Assert\Expression(
    'this.intVal != null',
    message: 'Expression failed, intVal is null'
)]
class MainDto extends AbstractDto
{
    #[Assert\NotNull]
    #[Assert\GreaterThanOrEqual(15)]
    public int|null $intVal = null;

    /**
     * @var list<SubDto>
     */
    #[Assert\Valid]
    #[Assert\Count(min: 1)]
    public array $dto = [];
}
