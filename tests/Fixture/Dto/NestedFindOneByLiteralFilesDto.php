<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Fixture\Dto;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use Symfony\Component\Validator\Constraints as Assert;

class NestedFindOneByLiteralFilesDto extends AbstractDto
{
    /**
     * @var list<NestedFindOneByLiteralLeafDto>
     */
    #[Assert\Valid]
    public array $files = [];
}
