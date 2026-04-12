<?php

declare(strict_types=1);

namespace DualMedia\DtoRequestBundle\Tests\Fixture\Dto;

use DualMedia\DtoRequestBundle\Dto\AbstractDto;
use DualMedia\DtoRequestBundle\Dto\Attribute\Bag;
use DualMedia\DtoRequestBundle\Metadata\Enum\BagEnum;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploadedFileDto extends AbstractDto
{
    #[Bag(BagEnum::Files)]
    public UploadedFile|null $file = null;

    /**
     * @var list<UploadedFile>
     */
    #[Bag(BagEnum::Files)]
    public array $files = [];
}
