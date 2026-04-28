<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Fixture\Dto;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use Symfony\Component\Validator\Constraints as Assert;

class NestedFindOneByLiteralRootDto extends AbstractDto
{
    /**
     * @var list<NestedFindOneByLiteralFilesDto>
     */
    #[Assert\Valid]
    public array $attachments = [];
}
